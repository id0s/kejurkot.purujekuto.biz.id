/**
 * Kejurwil Atletik - Main Application JS
 */

$(document).ready(function () {
    // 1. Mobile Sidebar Toggle
    $('#sidebarToggle').on('click', function () {
        $('#sidebar').toggleClass('show');
    });

    // Close sidebar when clicking outside on mobile
    $(document).on('click', function (e) {
        if ($(window).width() < 992) {
            if (!$(e.target).closest('#sidebar').length && !$(e.target).closest('#sidebarToggle').length) {
                $('#sidebar').removeClass('show');
            }
        }
    });

    // 2. DataTables Default Initializer
    if ($.fn.DataTable) {
        $('.datatable').DataTable({
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Cari data...",
                lengthMenu: "Tampil _MENU_ data",
                info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 s/d 0 dari 0 data",
                infoFiltered: "(disaring dari _MAX_ total data)",
                zeroRecords: "Tidak ada data yang cocok",
                paginate: {
                    first: '<i class="bi bi-chevron-double-left"></i>',
                    last: '<i class="bi bi-chevron-double-right"></i>',
                    previous: '<i class="bi bi-chevron-left"></i>',
                    next: '<i class="bi bi-chevron-right"></i>'
                }
            },
            pageLength: 25,
            responsive: true,
            order: []
        });
    }

    // 3. Auto-hide flash alerts after 5 seconds
    setTimeout(function () {
        $('.alert-dismissible').fadeOut('slow');
    }, 5000);

    // 4. Delete Confirmation Handler
    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        const url = $(this).attr('href') || $(this).data('url');
        const title = $(this).data('title') || 'data ini';

        $('#deleteModalTitle').text(title);
        $('#deleteForm').attr('action', url);
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        deleteModal.show();
    });

    // 5. Quick Edit No Dada Handler
    $(document).on('click', '.btn-quick-nodada', function (e) {
        e.preventDefault();
        const atletId   = $(this).data('atlet-id');
        const atletName = $(this).data('atlet-name');
        const noDada    = $(this).data('no-dada') || '';

        $('#quickAtletId').val(atletId);
        $('#quickAtletName').text(atletName);
        $('#quickNoDadaInput').val(noDada);

        const modalEl = document.getElementById('modalQuickNoDada');
        if (modalEl) {
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
            setTimeout(function () {
                $('#quickNoDadaInput').focus().select();
            }, 350);
        }
    });

    $('#formQuickNoDada').on('submit', function (e) {
        e.preventDefault();
        const btn = $('#btnSaveQuickNoDada');
        const atletId = $('#quickAtletId').val();
        const noDada  = $('#quickNoDadaInput').val().trim();

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

        // Determine base url from existing links or origin
        const actionUrl = $('meta[name="base-url"]').attr('content') ? $('meta[name="base-url"]').attr('content') + '/atlet/update-no-dada' : '/atlet/update-no-dada';

        $.ajax({
            url: actionUrl,
            type: 'POST',
            data: {
                atlet_id: atletId,
                no_dada: noDada
            },
            dataType: 'json',
            success: function (res) {
                btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i> Simpan No Dada');
                const modalEl = document.getElementById('modalQuickNoDada');
                if (modalEl) {
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                }

                // Update badge on all occurrences of this athlete on screen
                const targets = $('.btn-quick-nodada[data-atlet-id="' + atletId + '"]');
                targets.data('no-dada', noDada);
                targets.closest('td').attr('data-nodada', noDada || '999999');
                if (noDada) {
                    targets.removeClass('badge-secondary text-muted').addClass('badge-amber').html(noDada + ' <i class="bi bi-pencil-fill ms-1 opacity-50" style="font-size: 8px;"></i>');
                } else {
                    targets.removeClass('badge-amber').addClass('badge-secondary').html('- <i class="bi bi-pencil-fill ms-1 opacity-50" style="font-size: 8px;"></i>');
                }
            },
            error: function (err) {
                btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i> Simpan No Dada');
                alert('Gagal menyimpan No Dada. Silakan coba lagi.');
            }
        });
    });

    // 6. Solid Dark / Light Theme Toggle Handler
    function initThemeToggle() {
        const getStoredTheme = () => {
            try { return localStorage.getItem('kejurwil_theme'); } catch(e) { return null; }
        };
        const setStoredTheme = theme => {
            try { localStorage.setItem('kejurwil_theme', theme); } catch(e) {}
        };

        const getPreferredTheme = () => {
            const stored = getStoredTheme();
            if (stored === 'dark' || stored === 'light') return stored;
            return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        };

        const updateToggleButtons = function (theme) {
            const isDark = theme === 'dark';
            $('.theme-icon-dark').toggle(isDark);
            $('.theme-icon-light').toggle(!isDark);
            $('.theme-label').text(isDark ? 'Gelap' : 'Terang');
            $('#themeToggleBtn, #themeToggleBtnHome').attr('title', isDark ? 'Beralih ke Mode Terang' : 'Beralih ke Mode Gelap');
        };

        const setTheme = function (theme) {
            const isDark = theme === 'dark';
            document.documentElement.setAttribute('data-bs-theme', theme);
            if (document.body) {
                document.body.classList.toggle('dark-theme', isDark);
            }
            document.documentElement.classList.toggle('dark-theme', isDark);
            setStoredTheme(theme);
            updateToggleButtons(theme);
        };

        if (!window.toggleKejurwilTheme) {
            let isToggling = false;
            window.toggleKejurwilTheme = function(e) {
                if (e) {
                    if (typeof e.preventDefault === 'function') e.preventDefault();
                    if (typeof e.stopPropagation === 'function') e.stopPropagation();
                }
                if (isToggling) return;
                isToggling = true;
                setTimeout(() => { isToggling = false; }, 200);

                const isDarkNow = document.documentElement.getAttribute('data-bs-theme') === 'dark' ||
                                  (document.body && document.body.classList.contains('dark-theme')) ||
                                  document.documentElement.classList.contains('dark-theme');
                const newTheme = isDarkNow ? 'light' : 'dark';
                setTheme(newTheme);
            };
        }

        // Attach SINGLE click handler with namespace and stopPropagation
        $(document).off('click.theme', '.theme-toggle-btn')
                   .on('click.theme', '.theme-toggle-btn', function (e) {
            e.preventDefault();
            e.stopPropagation();
            window.toggleKejurwilTheme(e);
        });

        // Sync initial theme
        const initialTheme = getPreferredTheme();
        setTheme(initialTheme);

        // Listen for OS theme changes if user hasn't explicitly set theme
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
            if (!getStoredTheme()) {
                const osTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                setTheme(osTheme);
            }
        });
    }

    initThemeToggle();
});


