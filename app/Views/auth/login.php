<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Log in to your account — Kejurwil Atletik') ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap-icons.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/custom.css?v=' . filemtime(FCPATH . 'assets/css/custom.css')) ?>">

    <style>
        body {
            background-color: #F4F5F7 !important;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1.5rem;
            color: #172B4D;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            background: #FFFFFF;
            border: 1px solid #DFE1E6;
            border-radius: 4px;
            padding: 2.25rem 2.5rem;
            box-shadow: 0 4px 16px rgba(9, 30, 66, 0.12);
        }
        .login-icon {
            width: 44px;
            height: 44px;
            border-radius: 4px;
            background: #0052CC;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.35rem;
            margin: 0 auto 1rem;
            box-shadow: 0 1px 3px rgba(9, 30, 66, 0.2);
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="text-center mb-4">
        <div class="login-icon">
            <i class="bi bi-trophy-fill"></i>
        </div>
        <h4 class="fw-bold mb-1" style="color: #172B4D; font-size: 19px; letter-spacing: -0.2px;">Kejurwil Atletik</h4>
        <p class="text-muted small mb-0">Log in to your admin workspace</p>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger py-2 px-3 small rounded-1 mb-3">
            <i class="bi bi-exclamation-circle-fill me-1"></i> <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success py-2 px-3 small rounded-1 mb-3">
            <i class="bi bi-check-circle-fill me-1"></i> <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <form action="<?= base_url('auth/login') ?>" method="post">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label class="form-label" style="font-size: 11.5px; font-weight: 700; color: #5E6C84; text-transform: uppercase;">Username</label>
            <input type="text" name="username" class="form-control" placeholder="Enter username" value="<?= old('username', 'admin') ?>" required autofocus>
        </div>

        <div class="mb-4">
            <label class="form-label" style="font-size: 11.5px; font-weight: 700; color: #5E6C84; text-transform: uppercase;">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Enter password" value="admin123" required>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold" style="font-size: 14px;">
            Log In
        </button>

        <div class="mt-4 p-2 rounded-1 text-center" style="background: #FAFBFC; border: 1px solid #EBECF0;">
            <div class="text-muted" style="font-size: 11.5px;">
                <i class="bi bi-info-circle me-1 text-primary"></i> Default credentials:
                <div class="mt-1" style="color: #172B4D;">Username: <strong>admin</strong> | Password: <strong>admin123</strong></div>
            </div>
        </div>
    </form>
</div>

</body>
</html>
