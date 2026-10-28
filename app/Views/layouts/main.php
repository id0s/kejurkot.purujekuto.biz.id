<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Sistem Kejurwil Atletik') ?> — Manajemen Perlombaan</title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/images/pasi-logo.png') ?>">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Vendor CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap-icons.min.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/dataTables.bootstrap5.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/select2.min.css') ?>">
    
    <!-- Custom CSS (Jira SaaS Theme with Cache Buster) -->
    <link rel="stylesheet" href="<?= base_url('assets/css/custom.css?v=' . filemtime(FCPATH . 'assets/css/custom.css')) ?>">
    
    <!-- Early Theme Initializer & Fast Vanilla Engine -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('kejurwil_theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
            if (savedTheme === 'dark') {
                document.documentElement.classList.add('dark-theme');
            }
        })();

        function toggleKejurwilTheme(e) {
            if (e) {
                if (typeof e.preventDefault === 'function') e.preventDefault();
                if (typeof e.stopPropagation === 'function') e.stopPropagation();
            }
            const html = document.documentElement;
            const isDark = html.getAttribute('data-bs-theme') === 'dark' || html.classList.contains('dark-theme');
            const next = isDark ? 'light' : 'dark';

            html.setAttribute('data-bs-theme', next);
            html.classList.toggle('dark-theme', next === 'dark');
            if (document.body) {
                document.body.classList.toggle('dark-theme', next === 'dark');
            }
            try {
                localStorage.setItem('kejurwil_theme', next);
            } catch(err) {}

            document.querySelectorAll('.theme-icon-dark').forEach(function(el) {
                el.style.display = next === 'dark' ? 'inline-block' : 'none';
            });
            document.querySelectorAll('.theme-icon-light').forEach(function(el) {
                el.style.display = next === 'dark' ? 'none' : 'inline-block';
            });
            document.querySelectorAll('.theme-label').forEach(function(el) {
                el.textContent = next === 'dark' ? 'Gelap' : 'Terang';
            });
            const btn = document.getElementById('themeToggleBtnHome') || document.getElementById('themeToggleBtn');
            if (btn) btn.setAttribute('title', next === 'dark' ? 'Beralih ke Mode Terang' : 'Beralih ke Mode Gelap');
            return false;
        }
        window.toggleKejurwilTheme = toggleKejurwilTheme;
    </script>

    <?= $this->renderSection('styles') ?>
</head>
<body>

<div id="wrapper">
    <!-- Sidebar -->
    <?= $this->include('components/sidebar') ?>

    <!-- Main Content -->
    <div id="main-content">
        <!-- Topbar -->
        <?= $this->include('components/topbar') ?>

        <!-- Page Container -->
        <main class="container-fluid p-4">
            <!-- Flash Messages -->
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 bg-success bg-opacity-25 text-success-emphasis d-flex align-items-center mb-4" role="alert">
                    <i class="bi bi-check-circle-fill fs-5 me-2"></i>
                    <div><?= session()->getFlashdata('success') ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 bg-danger bg-opacity-25 text-danger-emphasis d-flex align-items-center mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                    <div><?= session()->getFlashdata('error') ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('errors')): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 bg-danger bg-opacity-25 text-danger-emphasis mb-4" role="alert">
                    <div class="d-flex align-items-center mb-1">
                        <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                        <strong>Terdapat kesalahan:</strong>
                    </div>
                    <ul class="mb-0 ps-4">
                        <?php foreach (session()->getFlashdata('errors') as $err): ?>
                            <li><?= esc($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Page Content -->
            <?= $this->renderSection('content') ?>
        </main>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<?= $this->include('components/modal_delete') ?>

<!-- Quick Edit No Dada Modal -->
<?= $this->include('components/modal_quick_nodada') ?>

<!-- Vendor Scripts -->
<script src="<?= base_url('assets/js/jquery.min.js') ?>"></script>
<script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= base_url('assets/js/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/js/dataTables.bootstrap5.min.js') ?>"></script>
<script src="<?= base_url('assets/js/select2.min.js') ?>"></script>
<script src="<?= base_url('assets/js/chart.min.js') ?>"></script>

<!-- Custom App JS -->
<script src="<?= base_url('assets/js/app.js') ?>"></script>

<?= $this->renderSection('scripts') ?>

</body>
</html>
