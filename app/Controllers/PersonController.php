<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Person;
use App\Support\Text;

/** صفحه‌ی تعریف اشخاص (اطلاعات پایه) */
final class PersonController extends Controller
{
    private const CODE_PATTERN = '/^[0-9A-Za-z][0-9A-Za-z.\-]{0,19}$/';
    private const ECONOMIC_CODE_PATTERN = '/^[0-9]{0,20}$/';

    public function index(): void
    {
        $search = $this->queryString('q', 100);
        $type = $this->queryString('type', 20);
        $this->render('persons/index', [
            'title'   => 'اشخاص',
            'persons' => Person::all($search, $type),
            'search'  => $search,
            'type'    => $type,
        ]);
    }

    public function create(): void
    {
        $this->render('persons/form', [
            'title'  => 'شخص جدید',
            'person' => $this->blank(Person::nextCode()),
            'action' => 'persons/store',
        ]);
    }

    public function store(): void
    {
        [$errors, $data] = $this->validated(null);
        if ($errors !== []) {
            http_response_code(422);
            $this->render('persons/form', [
                'title'  => 'شخص جدید',
                'person' => $data,
                'action' => 'persons/store',
                'errors' => $errors,
            ]);
            return;
        }

        Person::create($data);
        flash('success', 'شخص با موفقیت ثبت شد.');
        redirect('persons');
    }

    public function edit(): void
    {
        $person = Person::find($this->queryId());
        if ($person === null) {
            $this->notFound();
        }

        $this->render('persons/form', [
            'title'  => 'ویرایش شخص',
            'person' => $person,
            'action' => 'persons/update',
        ]);
    }

    public function update(): void
    {
        $id = $this->queryId();
        $existing = Person::find($id);
        if ($existing === null) {
            $this->notFound();
        }

        [$errors, $data] = $this->validated($id);
        if ($errors !== []) {
            http_response_code(422);
            $this->render('persons/form', [
                'title'  => 'ویرایش شخص',
                'person' => array_merge($existing, $data),
                'action' => 'persons/update',
                'errors' => $errors,
            ]);
            return;
        }

        Person::update($id, $data);
        flash('success', 'تغییرات شخص ذخیره شد.');
        redirect('persons');
    }

    public function toggle(): void
    {
        $person = Person::find($this->queryId());
        if ($person === null) {
            $this->notFound();
        }

        Person::setActive($person['id'], !$person['is_active']);
        flash('success', $person['is_active'] ? 'شخص غیرفعال شد.' : 'شخص فعال شد.');
        redirect('persons');
    }

    /** @return array<string,string> */
    private function blank(string $code): array
    {
        return [
            'id' => 0, 'code' => $code, 'name' => '', 'person_type' => 'customer',
            'national_id' => '', 'economic_code' => '', 'phone' => '', 'mobile' => '',
            'email' => '', 'address' => '', 'notes' => '',
        ];
    }

    /** @return array{0: list<string>, 1: array<string,string>} */
    private function validated(?int $exceptId): array
    {
        $data = [
            'code'          => Text::toEnglishDigits($this->postString('code', 20)),
            'name'          => $this->postString('name', 150),
            'person_type'   => $this->postString('person_type', 20),
            'national_id'   => Text::toEnglishDigits($this->postString('national_id', 20)),
            'economic_code' => Text::toEnglishDigits($this->postString('economic_code', 20)),
            'phone'         => Text::toEnglishDigits($this->postString('phone', 30)),
            'mobile'        => Text::toEnglishDigits($this->postString('mobile', 30)),
            'email'         => $this->postString('email', 150),
            'address'       => $this->postString('address', 500),
            'notes'         => $this->postString('notes', 500),
        ];

        $errors = [];
        if (!preg_match(self::CODE_PATTERN, $data['code'])) {
            $errors[] = 'کد شخص باید حداکثر ۲۰ کاراکتر (حروف لاتین، عدد، نقطه یا خط تیره) باشد.';
        } elseif (Person::codeExists($data['code'], $exceptId)) {
            $errors[] = 'کد شخص تکراری است.';
        }
        if ($data['name'] === '') {
            $errors[] = 'نام شخص را وارد کنید.';
        }
        if (!array_key_exists($data['person_type'], Person::TYPES)) {
            $errors[] = 'نوع شخص نامعتبر است.';
        }
        if ($data['national_id'] !== '' && !Person::isValidNationalId($data['national_id'])) {
            $errors[] = 'کد ملی باید ۱۰ رقم (شخص حقیقی) یا شناسه ملی باید ۱۱ رقم (شخص حقوقی) باشد.';
        }
        if (preg_match(self::ECONOMIC_CODE_PATTERN, $data['economic_code']) !== 1) {
            $errors[] = 'کد اقتصادی فقط باید شامل ارقام باشد.';
        }
        if ($data['phone'] !== '' && !Person::isValidPhone($data['phone'])) {
            $errors[] = 'شماره تلفن نامعتبر است (ارقام، فاصله، + یا خط تیره).';
        }
        if ($data['mobile'] !== '' && !Person::isValidMobile($data['mobile'])) {
            $errors[] = 'شماره موبایل باید با ۰۹ شروع شود و ۱۱ رقم باشد.';
        }
        if ($data['email'] !== '' && filter_var($data['email'], FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = 'نشانی ایمیل نامعتبر است.';
        }

        $data['id'] = $exceptId ?? 0;
        return [$errors, $data];
    }
}
