<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            // Dashboard
            [1, 'dashboard', 'Dashboard', 0], // done
            [2, 'dashboard_access', 'Dashboard Access', 1], // done
            [119, 'search', 'Search', 1], // done

            // Administration
            [3, 'administration', 'Administration', 0], // done
            [4, 'admin_user_list', 'Admin User List', 3], // done
            [5, 'admin_user_add', 'Admin User Add', 3], // done
            [6, 'admin_user_edit', 'Admin User Edit', 3], // done
            [7, 'admin_user_delete', 'Admin User Delete', 3], // done

            // Role & Permission
            [8, 'role', 'Role & Permission', 0],
            [9, 'role_list', 'Role List', 8], // done
            [10, 'role_add', 'Role Add', 8], // done
            [11, 'role_edit', 'Role Edit', 8], // done
            [12, 'role_delete', 'Role Delete', 8], // done
            [13, 'role_view', 'Role View', 8], // done
            [14, 'permission_list', 'Permission List', 8], // done
            [15, 'permission_add', 'Permission Add', 8], // done
            [16, 'permission_edit', 'Permission Edit', 8], // done
            [17, 'permission_delete', 'Permission Delete', 8], // done

            // customer
            [18, 'customer', 'Customer', 0],
            [19, 'customer_list', 'Customer List', 18], // done
            [20, 'customer_add', 'Customer Add', 18], // done
            [21, 'customer_edit', 'Customer Edit', 18], // done
            [22, 'customer_delete', 'Customer Delete', 18], // done

            // supplier
            [23, 'supplier', 'Supplier', 0],
            [24, 'supplier_list', 'Supplier List', 23], // done
            [25, 'supplier_add', 'Supplier Add', 23], // done
            [26, 'supplier_edit', 'Supplier Edit', 23], // done
            [27, 'supplier_delete', 'Supplier Delete', 23], // done

            // products and others
            [28, 'product', 'Product', 0],
            [108, 'warehouse_list', 'Warehouse List', 28], // done
            [109, 'warehouse_add', 'Warehouse Add', 28], // done
            [110, 'warehouse_edit', 'Warehouse Edit', 28], // done
            [111, 'warehouse_delete', 'Warehouse Delete', 28], // done

            [29, 'unit_list', 'Unit List', 28], // done
            [30, 'unit_add', 'Unit Add', 28], // done
            [31, 'unit_edit', 'Unit Edit', 28], // done
            [32, 'unit_delete', 'Unit Delete', 28], // done

            [33, 'category_list', 'Category List', 28],
            [34, 'category_add', 'Category Add', 28], // done
            [35, 'category_edit', 'Category Edit', 28], // done
            [36, 'category_delete', 'Category Delete', 28], // done

            [37, 'brand_list', 'Brand List', 28], // done
            [38, 'brand_add', 'Brand Add', 28], // done
            [39, 'brand_edit', 'Brand Edit', 28], // done
            [40, 'brand_delete', 'Brand Delete', 28], // done

            [41, 'product_list', 'Product List', 28], // done
            [42, 'product_add', 'Product Add', 28], // done
            [43, 'product_edit', 'Product Edit', 28], // done
            [44, 'product_delete', 'Product Delete', 28], // done

            [130, 'attribute_list', 'Product Attribute', 28], // done
            [125, 'attribute_add', 'Product Attribute Add', 28], // done
            [126, 'attribute_edit', 'Product Attribute Edit', 28], // done
            [127, 'attribute_delete', 'Product Attribute Delete', 28], // done

            [45, 'combo_product_list', 'Combo Product List', 28],
            [46, 'combo_product_add', 'Combo Product Add', 28], // done
            [47, 'combo_product_edit', 'Combo Product Edit', 28], // done
            [48, 'combo_product_delete', 'Combo Product Delete', 28], // done

            [114, 'product_transfer_list', 'Product Transfer List', 28], // done
            [115, 'product_transfer_delete', 'Product Transfer Delete', 28], // done
            [116, 'salary_payment', 'Salary Due Payment', 78], // done

            // stock
            [121, 'product_stock_adjustment_list', 'Product Stock Adjustment List', 49], // done
            [122, 'payment_method_list', 'Payment Method List', 90], // done
            [123, 'account_management', 'Account Management', 61], // done
            [129, 'purchases_price_show', 'Purchases Price Show', 28], // done

            // purchase
            [49, 'purchase', 'Purchase', 0],
            [50, 'purchases_list', 'Purchase List', 49], // done
            [51, 'purchase_add', 'Purchase Add', 49], // done
            [52, 'purchase_edit', 'Purchase Edit', 49], // done
            [53, 'purchase_delete', 'Purchase Delete', 49], // done
            [54, 'due_purchase_list', 'Due Purchase List', 49], // done
            [113, 'due_purchase_payment', 'Due Purchase Payment', 49], // done
            [55, 'stock_list', 'Stock List', 49], // done

            [124, 'purchase_return', 'Purchases Return', 49], // done
            [128, 'stock_report_list', 'Stock Report', 49],

            [133, 'Sale_return_adjustment', 'Sale Return Adjustment', 49], // done
            [134, 'view_own_sales', 'View Own Sales', 56], // done
            [135, 'view_all_sales', 'View All Sales', 49], // done

            [136, 'owner_capital', 'Owner Capital', 61],
            [137, 'withdraw_fund', 'Withdraw Fund', 61],
            [138, 'add_fund', 'ADD Fund', 61],
            [139, 'transfer_fund', 'Transfer Fund', 61],

            // orders
            [56, 'order', 'Order', 0],
            [57, 'order_list', 'Order List', 56], // done
            [58, 'order_add', 'Order Add', 56], // done
            [59, 'order_edit', 'Order Edit', 56], // done
            [60, 'order_delete', 'Order Delete', 56], // done
            [120, 'recent_order', 'Recent Order', 56], // done

            // accounts
            [61, 'accounts', 'Accounts', 0],
            [62, 'sales_ledger', 'Sales Ledger', 61], // done
            [63, 'purchases_ledger', 'Purchases Ledger', 61], // done
            [112, 'cash_flow', 'Cash Flow', 61], // done

            [131, 'expense_ledger', 'Expense Ledger', 61], // done
            [132, 'general_ledger', 'General Ledger', 61], // done

            // employee
            [64, 'employee', 'Employee', 0],
            [65, 'employee_list', 'Employee List', 64], // done
            [66, 'employee_add', 'Employee Add', 64], // done
            [67, 'employee_edit', 'Employee Edit', 64], // done
            [68, 'employee_delete', 'Employee Delete', 64], // done

            [69, 'expense', 'Expense', 0],
            [70, 'expense_category_list', 'Expense Category List', 69], // done
            [71, 'expense_category_add', 'Expense Category Add', 69], // done
            [72, 'expense_category_edit', 'Expense Category Edit', 69], // done
            [73, 'expense_category_delete', 'Expense Category Delete', 69], // done
            [74, 'expense_list', 'Expense List', 69], // done
            [75, 'expense_add', 'Expense Add', 69], // done
            [76, 'expense_edit', 'Expense Edit', 69], // done
            [77, 'expense_delete', 'Expense Delete', 69], // done
            [78, 'salary', 'Salary', 0],
            [79, 'salary_list', 'Salary List', 78], // done
            [80, 'salary_add', 'Salary Add', 78], // done
            [81, 'salary_edit', 'Salary Edit', 78], // done
            [82, 'salary_delete', 'Salary Delete', 78], // done

            [83, 'report', 'Report', 0],
            [84, 'sales_report_list', 'Sales Report List', 83], // done
            [85, 'purchases_report_list', 'Purchases Report List', 83], // done
            [117, 'expense_report_list', 'Expense Report List', 83], // done
            [118, 'daily_report', 'Daily Report', 83], // done
            [140, 'profit_loss_report', 'Profit Loss Report', 83], // done

            // System Settings
            [90, 'system_setting', 'General Setting', 0],
            [91, 'site_setting', 'General Setting', 90],
            [92, 'integration_access', 'Integration Access', 90],
            [93, 'database_backup', 'Database Backup', 90],
            [87, 'email_configure', 'Email Configure', 90],
            [88, 'email_template', 'Email Template', 90],
            [89, 'email_template_edit', 'Email Template Edit', 90],

            // language
            [94, 'language', 'Language', 0],
            [95, 'language_list', 'Language List', 94],
            [96, 'language_add', 'Language Add', 94],
            [97, 'language_edit', 'Language Edit', 94],
            [98, 'language_delete', 'Language Delete', 94],
            [99, 'language_import', 'Language Import', 94],
            [100, 'language_import_update', 'Language Import Update', 94],
            [107, 'cache_clear', 'Application Cache Clear', 94],

            // activities
            [101, 'activities', 'Activities', 0],
            [102, 'download_manager_list', 'Download Manager List', 101],
            [103, 'notification_center_list', 'Notification Center List', 101],
            [104, 'recycle_bin_list', 'Recycle Bin List', 101],
            [105, 'activity_log_list', 'Activity Log List', 101],
            [106, 'notification_view', 'Notification', 101],

        ];

        foreach ($permissions as $permissionData) {
            Permission::updateOrCreate(
                ['id' => $permissionData[0]],
                [
                    'name' => $permissionData[1],
                    'display_name' => $permissionData[2],
                    'submodule_id' => $permissionData[3],
                    'guard_name' => 'admin',
                ]
            );
        }

        $user = Admin::where('email', 'admin@gmail.com')->first();
        if (! $user) {
            $user = new Admin();
            $user->name = 'Administrator';
            $user->username = 'admin';
            $user->email = 'admin@gmail.com';
            $user->phone = '01303576765';
            $user->password = Hash::make('admin');
            $user->save();
        }

        $permissions = [];
        $allPermissions = Permission::all();
        foreach ($allPermissions as $permission) {
            array_push($permissions, $permission->id);
        }

        $role = Role::where('name', 'admin')->first();
        if (! $role) {
            $role = new Role();
            $role->name = 'Admin';
            $role->guard_name = 'admin';
            $role->save();
        }

        foreach ($permissions as $item) {
            $role->givePermissionTo($item);
        }

        $user->assignRole($role);
    }
}
