<?php
declare(strict_types=1);

/**
 * جدول مسیرها:
 *   'نام' => [کنترلر, متد, فعل HTTP, سطح دسترسی]
 * سطح دسترسی: guest (بدون ورود) | view (هر کاربر واردشده) | write (حسابدار/مدیر) | admin (مدیر)
 */

use App\Controllers\AccountController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\PersonController;
use App\Controllers\ProfileController;
use App\Controllers\ReportController;
use App\Controllers\SetupController;
use App\Controllers\UserController;
use App\Controllers\VoucherController;

return [
    'setup'           => [SetupController::class, 'form', 'GET', 'guest'],
    'setup/store'     => [SetupController::class, 'store', 'POST', 'guest'],

    'login'           => [AuthController::class, 'login', 'GET', 'guest'],
    'login/submit'    => [AuthController::class, 'authenticate', 'POST', 'guest'],
    'logout'          => [AuthController::class, 'logout', 'POST', 'view'],

    'dashboard'       => [DashboardController::class, 'index', 'GET', 'view'],

    'accounts'        => [AccountController::class, 'index', 'GET', 'view'],
    'accounts/create' => [AccountController::class, 'create', 'GET', 'write'],
    'accounts/store'  => [AccountController::class, 'store', 'POST', 'write'],
    'accounts/edit'   => [AccountController::class, 'edit', 'GET', 'write'],
    'accounts/update' => [AccountController::class, 'update', 'POST', 'write'],
    'accounts/toggle' => [AccountController::class, 'toggle', 'POST', 'write'],
    'accounts/delete' => [AccountController::class, 'destroy', 'POST', 'write'],
    'persons'         => [PersonController::class, 'index', 'GET', 'view'],
    'persons/create'  => [PersonController::class, 'create', 'GET', 'write'],
    'persons/store'   => [PersonController::class, 'store', 'POST', 'write'],
    'persons/edit'    => [PersonController::class, 'edit', 'GET', 'write'],
    'persons/update'  => [PersonController::class, 'update', 'POST', 'write'],
    'persons/toggle'  => [PersonController::class, 'toggle', 'POST', 'write'],

    'vouchers'        => [VoucherController::class, 'index', 'GET', 'view'],
    'vouchers/show'   => [VoucherController::class, 'show', 'GET', 'view'],
    'vouchers/create' => [VoucherController::class, 'create', 'GET', 'write'],
    'vouchers/store'  => [VoucherController::class, 'store', 'POST', 'write'],
    'vouchers/edit'   => [VoucherController::class, 'edit', 'GET', 'write'],
    'vouchers/update' => [VoucherController::class, 'update', 'POST', 'write'],
    'vouchers/delete' => [VoucherController::class, 'destroy', 'POST', 'write'],

    'reports/ledger'  => [ReportController::class, 'ledger', 'GET', 'view'],
    'reports/trial'   => [ReportController::class, 'trial', 'GET', 'view'],

    'users'           => [UserController::class, 'index', 'GET', 'admin'],
    'users/create'    => [UserController::class, 'create', 'GET', 'admin'],
    'users/store'     => [UserController::class, 'store', 'POST', 'admin'],
    'users/edit'      => [UserController::class, 'edit', 'GET', 'admin'],
    'users/update'    => [UserController::class, 'update', 'POST', 'admin'],

    'password'        => [ProfileController::class, 'form', 'GET', 'view'],
    'password/update' => [ProfileController::class, 'update', 'POST', 'view'],
];
