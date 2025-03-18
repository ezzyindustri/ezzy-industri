document.addEventListener('livewire:initialized', () => {
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
                Livewire.dispatch('showDefectModal');
            }
        });
    });
});