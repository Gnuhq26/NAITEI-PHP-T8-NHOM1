<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Feedback;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\StatusOrder;
use App\Repositories\CategoryRepository;

class AdminController extends Controller
{
    private const PRODUCT_IMAGE_DIR = 'images/products';

    protected $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'total_categories' => $this->categoryRepository->count(),
            'total_products' => Product::count(),
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('order_id', '>', 0)->count(),
            'total_feedbacks' => Feedback::count()
        ];
        
        return view('admin.pages.dashboard', compact('stats'));
    }

    public function dashboardStats()
    {
        $stats = [
            'total_users' => User::count(),
            'total_categories' => $this->categoryRepository->count(),
            'total_products' => Product::count(),
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('order_id', '>', 0)->count(),
            'total_feedbacks' => Feedback::count()
        ];
        
        return response()->json($stats);
    }

    public function getWeeklyChartData()
    {
        $days = [];
        $activeUsers = [];
        $orderedProducts = [];
        $newOrders = [];
        $newFeedbacks = [];
        $dailyRevenue = [];

        // get data for the last 7 days
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $days[] = $date->format('M d');
            $dateString = $date->format('Y-m-d');
            
            // the number of active users (who placed orders or left feedback ...)
            $usersWithOrders = User::whereHas('orders', function($query) use ($dateString) {
                $query->whereDate('order_date', $dateString);
            })->pluck('id');
            
            $usersWithFeedbacks = User::whereHas('feedbacks', function($query) use ($dateString) {
                $query->whereDate('created_at', $dateString);
            })->pluck('id');
            
            $activeUserCount = $usersWithOrders->merge($usersWithFeedbacks)->unique()->count();
            $activeUsers[] = $activeUserCount;
            
            // the number of products ordered in the day
            $orderedProductCount = OrderItem::whereHas('order', function($query) use ($dateString) {
                $query->whereDate('order_date', $dateString);
            })->sum('quantity');
            $orderedProducts[] = (int) $orderedProductCount;
            
            // the number of new orders in the day
            $newOrderCount = Order::whereDate('order_date', $dateString)->count();
            $newOrders[] = $newOrderCount;
            
            // the number of new feedbacks in the day
            $newFeedbackCount = Feedback::whereDate('created_at', $dateString)->count();
            $newFeedbacks[] = $newFeedbackCount;
            
            // daily revenue from delivered orders
            $revenue = Order::where('status', 'delivered')
                ->where(function($query) use ($dateString) {
                    $query->whereDate('updated_at', $dateString)
                          ->orWhere(function($subQuery) use ($dateString) {
                              $subQuery->whereDate('created_at', $dateString)
                                       ->where('status', 'delivered');
                          });
                })
                ->sum('total_cost');
            $dailyRevenue[] = (float) $revenue;
        }

        return response()->json([
            'days' => $days,
            'activeUsers' => $activeUsers,
            'orderedProducts' => $orderedProducts,
            'newOrders' => $newOrders,
            'newFeedbacks' => $newFeedbacks,
            'dailyRevenue' => $dailyRevenue
        ]);
    }

    public function users()
    {
        $users = User::orderBy('updated_at', 'desc')->paginate(10);
        $roles = Role::all(); // Lấy tất cả roles để tạo filter dropdown
        return view('admin.pages.users', compact('users', 'roles'));
    }

    public function storeUser(StoreUserRequest $request)
    {
        // only super admin can create users
        if (auth()->user()->email !== Role::SUPER_ADMIN) {
            return redirect()->route('admin.users')->with('error', 'Only the super admin can create new users.');
        }

        $validated = $request->validated();
        
        if (isset($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        }
        User::create($validated);
        return redirect()->route('admin.users')->with('success', 'User created successfully.');
    }

    public function updateUser(UpdateUserRequest $request, User $user)
    {
        // only super admin can update users
        if (auth()->user()->email !== Role::SUPER_ADMIN) {
            return redirect()->route('admin.users')->with('error', 'Only the super admin can edit users.');
        }

        // super admin cannot edit their own account
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users')->with('error', 'You cannot edit your own account.');
        }

        $validated = $request->validated();
        $user->update($validated);
        return redirect()->route('admin.users')->with('success', 'User updated successfully.');
    }

    public function deleteUser(User $user)
    {
        // only super admin can delete users
        if (auth()->user()->email !== Role::SUPER_ADMIN) {
            return response()->json([
                'success' => false,
                'message' => 'Only the super admin can delete users.'
            ], 403);
        }

        // Prevent deletion of current logged in user
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete your own account.'
            ], 403);
        }

        // only allow deletion of deactivated users
        if ($user->is_activate) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete active users. Please deactivate the user first.'
            ], 403);
        }

        $user->delete();
        return response()->json(['success' => true]);
    }

    public function searchUsers(Request $request)
    {
        $query = $request->input('query');
        $roleId = $request->input('role_id');
        
        $usersQuery = User::with('role');
        
        // search filter (name hoặc email)
        if (!empty($query)) {
            $usersQuery->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%");
            });
        }
        
        // role filter  
        if (!empty($roleId) && $roleId !== 'all') {
            $usersQuery->where('role_id', $roleId);
        }
        
        $users = $usersQuery->paginate(10);
        $roles = Role::all(); // Lấy tất cả roles để tạo filter dropdown
        
        // ensure that search parameters are kept in pagination links
        $users->appends($request->only(['query', 'role_id']));
        
        return view('admin.pages.users', compact('users', 'roles'));
    }

    /**
     * @group Admin Categories
     * @authenticated
     * Trang quản lý danh mục sản phẩm.
     * @response view admin.pages.categories
     */
    public function categories()
    {
        // count products in each category
        $categories = $this->categoryRepository->getCategoriesWithProductCount(10);
        return view('admin.pages.categories', compact('categories'));
    }

    /**
     * @group Admin Categories
     * @authenticated
     * Tạo mới danh mục sản phẩm.
     * @bodyParam name string required Tên danh mục. Example: Sofa
     * @bodyParam image file Ảnh danh mục.
     * @response 302 {"success": true}
     * @response 422 {"message": "The given data was invalid.", "errors": {"name": ["The name has already been taken."]}}
     */
    public function storeCategory(StoreCategoryRequest $request)
    {
        $validated = $request->validated();
        
        try {
            // if there is an image, validate it.
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $validated['image'] = $image;
            } else {
                $validated['image'] = null; // No image uploaded
            }
    
            $this->categoryRepository->createCategory($validated);
    
            return redirect()->route('admin.categories')->with('success', 'Category added successfully.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->withErrors(['image' => $e->getMessage()])->withInput();
        }
    }

    /**
     * @group Admin Categories
     * @authenticated
     * Cập nhật danh mục sản phẩm.
     * @urlParam category integer required The ID of the category to update. Example: 1
     * @bodyParam name string Tên danh mục. Example: Updated Sofa
     * @bodyParam image file Ảnh danh mục.
     * @response 302 {"success": true}
     * @response 422 {"message": "The given data was invalid.", "errors": {"name": ["The name has already been taken."]}}
     */
    public function updateCategory(UpdateCategoryRequest $request, Category $category)
    {
        $validated = $request->validated();

        try {
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $validated['image'] = $image;
            } 
            $this->categoryRepository->updateCategory($category->category_id, $validated);
            return redirect()->route('admin.categories')->with('success', 'Category updated.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->withErrors(['image' => $e->getMessage()])->withInput();
        }
    }

    /**
     * @group Admin Categories
     * @authenticated
     * Xóa danh mục sản phẩm.
     * @urlParam category integer required The ID of the category to delete. Example: 1
     * @response 302 {"success": true}
     * @response 400 {"success": false, "message": "Cannot delete category with associated products."}
     */
    public function deleteCategory(Category $category)
    {
        if (!$this->categoryRepository->deleteCategory($category->category_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with associated products.'
            ], 400);
        }

        return response()->json(['success' => true]);
    }

    public function products()
    {
        $products = Product::with('category')->orderBy('updated_at', 'desc')->paginate(10); // paginate products (10 per page)
        $categories = $this->categoryRepository->getAllCategories();
        return view('admin.pages.products', compact('products', 'categories'));
    }

    public function storeProduct(StoreProductRequest $request)
    {
        $validated = $request->validated();
        $imagePath = null;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $allowedExtensions = ['jpeg', 'jpg', 'png', 'gif', 'svg'];
            $extension = $image->guessExtension();
            if (!in_array($extension, $allowedExtensions)) {
                return redirect()->back()->withErrors(['image' => 'Invalid image extension.']);
            }
            $imageName = Str::uuid() . '.' . $extension;
            $image->move(public_path(self::PRODUCT_IMAGE_DIR), $imageName);
            $validated['image'] = self::PRODUCT_IMAGE_DIR . '/' . $imageName;
        }

        Product::create($validated);
        return redirect()->route('admin.products')->with('success', 'Product added successfully.');
    }

    public function updateProduct(UpdateProductRequest $request, $productId)
    {
        $product = Product::where('product_id', $productId)->firstOrFail();
        
        $validated = $request->validated();

        if ($request->hasFile('image')) {
            // delete the old image
            if ($product->image && File::exists(public_path($product->image))) {
                File::delete(public_path($product->image));
            }
            
            $image = $request->file('image');
            $imageName = Str::uuid() . '.' . $image->extension();
            $image->move(public_path(self::PRODUCT_IMAGE_DIR), $imageName);
            $validated['image'] = self::PRODUCT_IMAGE_DIR . '/' . $imageName;
        }

        $product->update($validated);
        return redirect()->route('admin.products')->with('success', 'Product updated successfully.');
    }

    public function deleteProduct($productId)
    { 
        $product = Product::where('product_id', $productId)->firstOrFail();
        
        // delete the old image
        if ($product->image) {
            if (File::exists(public_path($product->image))) {
                File::delete(public_path($product->image));
            }
        }
        
        $product->delete();
        return response()->json(['success' => true]);
    }

    public function searchProducts(Request $request)
    {
        $query = $request->input('query');
        $categoryId = $request->input('category_id');
        
        $productsQuery = Product::with('category');
        
        // search filter
        if (!empty($query)) {
            // $productsQuery->where(function($q) use ($query) {
            //     $q->where('name', 'like', "%{$query}%");
            // }); // use this if we want to search in multiple fields

            $productsQuery->where('name', 'like', "%{$query}%");
        }
        
        // category filter
        if (!empty($categoryId) && $categoryId !== 'all') {
            $productsQuery->where('category_id', $categoryId);
        }
        
        $products = $productsQuery->paginate(10);
        $categories = $this->categoryRepository->getAllCategories();
        
        // ensure that search parameters are kept in pagination links
        $products->appends($request->only(['query', 'category_id']));
        
        return view('admin.pages.products', compact('products', 'categories'));
    }

    public function orders(Request $request)
    {
        $query = Order::with('user')->orderBy('updated_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('order_date', '>=', $request->input('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->whereDate('order_date', '<=', $request->input('to_date'));
        }

        // Order by most recent first
        $orders = $query->orderBy('order_date', 'desc')->paginate(10);

        return view('admin.pages.orders', compact('orders'));
    }

    public function feedbacks()
    {
        $feedbacks = Feedback::with('user', 'product')->orderBy('updated_at', 'desc')->paginate(10);
        return view('admin.pages.feedbacks', compact('feedbacks'));
    }

    public function showFeedback(Feedback $feedback)
    {
        $feedback->load('user', 'product');
        return response()->json([
            'feedback' => $feedback,
            'user' => $feedback->user,
            'product' => $feedback->product
        ]);
    }

    public function deleteFeedback(Feedback $feedback)
    {
        $feedback->delete();
        return redirect()->route('admin.feedbacks')->with('success', 'Feedback deleted successfully!');
    }

    public function updateOrderStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'approved', 'rejected', 'delivering', 'delivered'])],
        ]);

        // check if the order is already in a final state (delivered or cancelled)
        if (in_array($order->status, ['delivered', 'cancelled'])) {
            return redirect()->route('admin.orders')->with('error', __('Cannot change status of delivered or cancelled orders.'));
        }

        // define valid status transitions
        $validTransitions = [
            'pending' => ['approved', 'rejected'],
            'approved' => ['delivering'],
            'rejected' => [], // rejected orders cannot be changed
            'delivering' => ['delivered'],
            'delivered' => [], // delivered orders cannot be changed
            'cancelled' => [] // cancelled orders cannot be changed (but only customers can cancel)
        ];

        $currentStatus = $order->status;
        $newStatus = $validated['status'];

        if (!in_array($newStatus, $validTransitions[$currentStatus] ?? [])) {
            $errorMessage = match($currentStatus) {
                'pending' => __('From Pending status, you can only approve or reject the order.'),
                'approved' => __('From Approved status, you can only change to Delivering.'),
                'rejected' => __('Rejected orders cannot be changed.'),
                'delivering' => __('From Delivering status, you can only mark as Delivered.'),
                default => __('Invalid status transition.')
            };
            
            return redirect()->route('admin.orders')->with('error', $errorMessage);
        }

        DB::transaction(function () use ($order, $validated) {
            $oldStatus = $order->status;
            $newStatus = $validated['status'];
            
            if ($oldStatus === $newStatus) {
                return; // No change
            }
            
            $order->update(['status' => $newStatus]);

            StatusOrder::create([
                'action_type' => $newStatus,
                'date' => now(),
                'admin_id' => auth()->id(),
                'order_id' => $order->getKey(),
            ]);
        });

        $statusMessage = match($validated['status']) {
            'pending' => __('Order status changed to Pending'),
            'approved' => __('Order has been approved'),
            'rejected' => __('Order has been rejected'),
            'delivering' => __('Order is now being delivered'),
            'delivered' => __('Order has been marked as delivered'),
            default => __('Order status updated')
        };

        return redirect()->route('admin.orders')->with('success', $statusMessage);
    }

    public function showOrderDetails(Order $order)
    {
        $order->load('user', 'orderItems.product');
        
        return response()->json([
            'order' => $order,
            'customer_name' => $order->user->name ?? $order->user->user_name ?? __('N/A'),
            'order_items' => $order->orderItems->map(function ($item) {
                return [
                    'product_name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'subtotal' => $item->quantity * $item->price,
                ];
            }),
        ]);
    }

    public function toggleUserActivation(User $user)
    {   
        // only super admin can toggle user activation
        if (auth()->user()->email !== Role::SUPER_ADMIN) {
            return response()->json([
                'success' => false,
                'message' => 'Only the super admin can activate/deactivate users.'
            ], 403);
        }

        // if the user is the currently logged in user, prevent deactivation
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot deactivate your own account.'
            ], 403);
        }

        $user->is_activate = !$user->is_activate;
        $user->save();

        $status = $user->is_activate ? 'activated' : 'deactivated';
        return response()->json([
            'success' => true,
            'message' => "User has been {$status} successfully.",
            'is_activate' => $user->is_activate
        ]);
    }
}
