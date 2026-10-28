<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Log in — Sistem Kejuaraan Olahraga') ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap-icons.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/custom.css?v=' . filemtime(FCPATH . 'assets/css/custom.css')) ?>">

    <style>
        body {
            background: linear-gradient(135deg, #091E42 0%, #172B4D 100%) !important;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1.5rem;
            color: #172B4D;
            font-family: 'Inter', sans-serif;
        }
        .login-wrapper {
            width: 100%;
            max-width: 440px;
        }
        .login-card {
            background: #FFFFFF;
            border-radius: 8px;
            padding: 2.25rem 2.25rem;
            box-shadow: 0 12px 36px rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .login-icon {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            background: linear-gradient(135deg, #0052CC 0%, #0747A6 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.6rem;
            margin: 0 auto 1rem;
            box-shadow: 0 4px 12px rgba(0, 82, 204, 0.35);
        }
        .demo-pill {
            cursor: pointer;
            transition: all 0.15s ease;
            font-size: 11.5px;
            border: 1px solid #DFE1E6;
            background: #FAFBFC;
            padding: 6px 10px;
            border-radius: 6px;
        }
        .demo-pill:hover {
            background: #EBECF0;
            border-color: #0052CC;
            color: #0052CC;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="login-card">
        <div class="text-center mb-4">
            <div class="login-icon">
                <i class="bi bi-trophy-fill"></i>
            </div>
            <h4 class="fw-bold mb-1" style="color: #091E42; font-size: 21px; letter-spacing: -0.3px;">Portal Manajemen Lomba</h4>
            <p class="text-muted small mb-0">Login Panitia & Admin Cabang Olahraga (Cabor)</p>
        </div>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger py-2 px-3 small rounded-2 mb-3 d-flex align-items-center">
                <i class="bi bi-exclamation-circle-fill me-2 fs-6"></i>
                <div><?= session()->getFlashdata('error') ?></div>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success py-2 px-3 small rounded-2 mb-3 d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-2 fs-6"></i>
                <div><?= session()->getFlashdata('success') ?></div>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('auth/login') ?>" method="post">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label" style="font-size: 11.5px; font-weight: 700; color: #5E6C84; text-transform: uppercase;">Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted"><i class="bi bi-person-fill"></i></span>
                    <input type="text" name="username" id="usernameInput" class="form-control" placeholder="Masukkan username" value="<?= old('username', '') ?>" required autofocus autocomplete="username">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label d-flex justify-content-between align-items-center" style="font-size: 11.5px; font-weight: 700; color: #5E6C84; text-transform: uppercase;">
                    <span>Password</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted"><i class="bi bi-lock-fill"></i></span>
                    <input type="password" name="password" id="passwordInput" class="form-control" placeholder="Masukkan password" value="" required autocomplete="current-password">
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold" style="font-size: 14.5px; border-radius: 6px;">
                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk ke Dashboard
            </button>

            <!-- Quick Login Helper (Akun Admin Cabor) -->
            <div class="mt-4 pt-3 border-top">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted fw-bold" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Akun Admin Cabor Atletik</span>
                    <small class="text-muted" style="font-size: 11px;">Klik untuk isi form</small>
                </div>
                
                <div class="d-flex flex-column gap-1.5">
                    <div class="demo-pill d-flex align-items-center justify-content-between" onclick="setCredentials('kejuaraan', 'atletik')">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-success text-white" style="font-size: 10px;">ADMIN ATLETIK</span>
                            <span class="fw-semibold text-dark">kejuaraan</span>
                        </div>
                        <span class="text-muted font-monospace" style="font-size: 11px;">atletik</span>
                    </div>
                </div>
            </div>

            <div class="text-center mt-3">
                <a href="<?= base_url('/') ?>" class="text-decoration-none text-muted small">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Halaman Publik
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function setCredentials(user, pass) {
    document.getElementById('usernameInput').value = user;
    document.getElementById('passwordInput').value = pass;
    document.getElementById('usernameInput').focus();
}
</script>

</body>
</html>
