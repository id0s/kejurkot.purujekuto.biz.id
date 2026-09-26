<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title d-flex align-items-center gap-2 text-danger" id="deleteModalLabel">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span>Konfirmasi Penghapusan</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <p class="mb-1 text-muted">Apakah Anda yakin ingin menghapus:</p>
                <div class="p-3 my-2 rounded bg-dark border border-danger border-opacity-25">
                    <strong id="deleteModalTitle" class="text-white"></strong>
                </div>
                <small class="text-secondary d-block mt-2">
                    <i class="bi bi-info-circle"></i> Tindakan ini tidak dapat dibatalkan dan semua relasi data terkait akan terpengaruh.
                </small>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary-custom" data-bs-dismiss="modal">Batal</button>
                <form id="deleteForm" method="post" action="">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-danger px-4" style="border-radius: 10px;">
                        <i class="bi bi-trash-fill me-1"></i> Hapus Sekarang
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
