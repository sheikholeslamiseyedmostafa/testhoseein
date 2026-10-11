<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Account;
use App\Support\Text;

final class AccountController extends Controller
{
    private const CODE_PATTERN = '/^[0-9A-Za-z][0-9A-Za-z.\-]{0,19}$/';

    public function index(): void
    {
        $search = $this->queryString('q', 100);
        $this->render('accounts/index', [
            'title'    => 'چارت حساب‌ها',
            'accounts' => Account::all($search),
            'search'   => $search,
        ]);
    }

    public function create(): void
    {
        $this->render('accounts/form', [
            'title'   => 'حساب جدید',
            'account' => ['id' => 0, 'code' => '', 'name' => '', 'type' => 'asset'],
            'action'  => 'accounts/store',
        ]);
    }

    public function store(): void
    {
        [$errors, $data] = $this->validated(null);
        if ($errors !== []) {
            http_response_code(422);
            $this->render('accounts/form', [
                'title'   => 'حساب جدید',
                'account' => $data,
                'action'  => 'accounts/store',
                'errors'  => $errors,
            ]);
            return;
        }

        Account::create($data['code'], $data['name'], $data['type']);
        flash('success', 'حساب با موفقیت ثبت شد.');
        redirect('accounts');
    }

    public function edit(): void
    {
        $account = Account::find($this->queryId());
        if ($account === null) {
            $this->notFound();
        }

        $this->render('accounts/form', [
            'title'   => 'ویرایش حساب',
            'account' => $account,
            'action'  => 'accounts/update',
        ]);
    }

    public function update(): void
    {
        $id = $this->queryId();
        $existing = Account::find($id);
        if ($existing === null) {
            $this->notFound();
        }

        [$errors, $data] = $this->validated($id);
        if ($errors !== []) {
            http_response_code(422);
            $this->render('accounts/form', [
                'title'   => 'ویرایش حساب',
                'account' => array_merge($existing, $data),
                'action'  => 'accounts/update',
                'errors'  => $errors,
            ]);
            return;
        }

        Account::update($id, $data['code'], $data['name'], $data['type']);
        flash('success', 'تغییرات حساب ذخیره شد.');
        redirect('accounts');
    }

    public function toggle(): void
    {
        $account = Account::find($this->queryId());
        if ($account === null) {
            $this->notFound();
        }

        Account::setActive($account['id'], !$account['is_active']);
        flash('success', $account['is_active'] ? 'حساب غیرفعال شد.' : 'حساب فعال شد.');
        redirect('accounts');
    }

    public function destroy(): void
    {
        $account = Account::find($this->queryId());
        if ($account === null) {
            $this->notFound();
        }

        if (Account::usageCount($account['id']) > 0) {
            flash('error', 'این حساب در اسناد استفاده شده و قابل حذف نیست؛ می‌توانید آن را غیرفعال کنید.');
        } else {
            Account::delete($account['id']);
            flash('success', 'حساب حذف شد.');
        }
        redirect('accounts');
    }

    /** @return array{0: list<string>, 1: array} */
    private function validated(?int $exceptId): array
    {
        $code = Text::toEnglishDigits($this->postString('code', 20));
        $name = $this->postString('name', 150);
        $type = $this->postString('type', 20);

        $errors = [];
        if (!preg_match(self::CODE_PATTERN, $code)) {
            $errors[] = 'کد حساب باید حداکثر ۲۰ کاراکتر (حروف لاتین، عدد، نقطه یا خط تیره) باشد.';
        } elseif (Account::codeExists($code, $exceptId)) {
            $errors[] = 'کد حساب تکراری است.';
        }
        if ($name === '') {
            $errors[] = 'نام حساب را وارد کنید.';
        }
        if (!array_key_exists($type, Account::TYPES)) {
            $errors[] = 'نوع حساب نامعتبر است.';
        }

        return [$errors, ['code' => $code, 'name' => $name, 'type' => $type]];
    }
}
