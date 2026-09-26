<!-- Modal Quick Edit No Dada Global -->
<div class="modal fade" id="modalQuickNoDada" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header py-2 px-3" style="background-color: #FEF3C7; border-bottom: 1px solid #FDE68A;">
                <h6 class="modal-title fw-bold mb-0" style="color: #92400E; font-size: 13px;">
                    <i class="bi bi-tag-fill text-warning me-1"></i> Ubah No Dada Atlet
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formQuickNoDada">
                <div class="modal-body p-3">
                    <input type="hidden" id="quickAtletId" name="atlet_id">
                    <div class="mb-2">
                        <label class="form-label text-muted small mb-0" style="font-size: 11px;">NAMA ATLET:</label>
                        <div class="fw-bold fs-6 text-truncate" id="quickAtletName" style="color: #172B4D;">-</div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold small mb-1" style="color: #172B4D;">Nomor Dada:</label>
                        <input type="text" id="quickNoDadaInput" name="no_dada" class="form-control form-control-lg font-monospace fw-bold text-center" placeholder="Misal: 101" style="letter-spacing: 2px; font-size: 1.35rem; border-color: #F59E0B; background-color: #FFFDF5;" autofocus>
                        <div class="form-text small text-muted mt-1" style="font-size: 11px;">Nomor identifikasi dada atlet untuk juri lintasan & pencatat waktu. Kosongkan jika ingin menghapus.</div>
                    </div>
                </div>
                <div class="modal-footer py-2 px-3 bg-light" style="border-top: 1px solid #DFE1E6;">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary px-3" id="btnSaveQuickNoDada">
                        <i class="bi bi-check-lg me-1"></i> Simpan No Dada
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
