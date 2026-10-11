<?php
/** @var array $users */
?>
<div class="page-head">
    <div>
        <h1>کاربران</h1>
        <p class="muted">مدیریت دسترسی کاربران به سیستم</p>
    </div>
    <a class="btn btn-primary" href="<?= e(url('users/create')) ?>">کاربر جدید</a>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>نام کاربری</th>
                <th>نام و نام خانوادگی</th>
                <th>نقش</th>
                <th>وضعیت</th>
                <th>تاریخ ایجاد</th>
                <th class="actions">عملیات</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr class="<?= $u['is_active'] ? '' : 'row-muted' ?>">
                    <td dir="ltr"><?= e($u['username']) ?></td>
                    <td><?= e($u['full_name']) ?></td>
                    <td><?= e(role_label($u['role'])) ?></td>
                    <td>
                        <?php if ($u['is_active']): ?>
                            <span class="badge badge-ok">فعال</span>
                        <?php else: ?>
                            <span class="badge">غیرفعال</span>
                        <?php endif; ?>
                    </td>
                    <td><?= jdate(substr((string) $u['created_at'], 0, 10)) ?></td>
                    <td class="actions"><a href="<?= e(url('users/edit', ['id' => $u['id']])) ?>">ویرایش</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
