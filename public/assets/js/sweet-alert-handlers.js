document.addEventListener('DOMContentLoaded', () => {
    let defectModal = document.getElementById('defectModal');
    if (defectModal) {
        defectModal = new bootstrap.Modal(defectModal, { keyboard: false, backdrop: 'static' });
    }


    // Handler untuk konfirmasi delete
    Livewire.on('show-delete-confirmation', () => {
        Swal.fire({
            title: 'Apakah anda yakin?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Livewire.dispatch('deleteConfirmed');
            }
        });
    });

    // Handler untuk pesan sukses setelah delete
    Livewire.on('deleted', () => {
        Swal.fire({
            title: 'Berhasil!',
            text: 'Data telah dihapus.',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        });
    });

    // Handler untuk konfirmasi quality check NG
    document.addEventListener('livewire:initialized', () => {
        const ngModal = new bootstrap.Modal(document.getElementById('ngFormModal'));

        Livewire.on('show-ng-confirmation', () => {
            Swal.fire({
                title: 'Perhatian!',
                text: 'Terdapat pengukuran yang tidak sesuai standar (NG). Apakah Anda yakin ingin melanjutkan?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Lanjutkan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    ngModal.show();
                }
            });
        });
    });


    Livewire.on('hideDefectModal', () => {
        if (defectModal) {
            defectModal.hide();
            setTimeout(() => { defectModal.dispose(); }, 500); // Reset modal biar nggak bug
        }
    });

});