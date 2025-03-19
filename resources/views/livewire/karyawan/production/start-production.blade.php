@push('styles')
    <link href="{{ asset('assets/css/custom/pages/start-production.css') }}" rel="stylesheet">
@endpush
<div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Mulai Produksi</h1>
        <nav>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('karyawan.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active">Mulai Produksi</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        @if(!$showChecksheet)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Form Mulai Produksi</h5>
                            
                            <div class="row justify-content-center">
                                <div class="col-md-8">
                                    <form wire:submit="startProduction">
                                        <div class="row mb-3">
                                            <label class="col-sm-3 col-form-label">Mesin</label>
                                            <div class="col-sm-9">
                                                <select class="form-select @error('selectedMachine') is-invalid @enderror" 
                                                    wire:model.live="selectedMachine">
                                                    <option value="">-- Pilih Mesin --</option>
                                                    @foreach($machines as $machine)
                                                        <option value="{{ $machine->id }}">{{ $machine->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('selectedMachine') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label class="col-sm-3 col-form-label">Shift</label>
                                            <div class="col-sm-9">
                                                <select class="form-select @error('selectedShift') is-invalid @enderror" 
                                                    wire:model.live="selectedShift">
                                                    <option value="">-- Pilih Shift --</option>
                                                    @foreach($shifts as $shift)
                                                        <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('selectedShift') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label class="col-sm-3 col-form-label">Produk</label>
                                            <div class="col-sm-9">
                                                <select class="form-select @error('product_id') is-invalid @enderror" 
                                                    wire:model.live="product_id">
                                                    <option value="">-- Pilih Produk --</option>
                                                    @foreach($products as $product)
                                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('product_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                        </div>

                                        <div class="text-center">
                                        <button type="button" 
                                            class="btn btn-primary px-5" 
                                            wire:click="startProduction"
                                            wire:loading.attr="disabled">
                                            <i class="bi bi-arrow-right-circle me-1"></i>
                                            <span wire:loading.remove wire:target="startProduction">
                                                LANJUT KE CHECKSHEET
                                            </span>
                                        </button>

                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($selectedMachine)
                <livewire:components.machine-sop-viewer 
                    :machine-id="$selectedMachine" 
                    wire:key="sop-{{ $selectedMachine }}" />
            @endif
        @else
            <livewire:components.checksheet-table 
                :machine-id="$selectedMachine" 
                :shift-id="$selectedShift" />
        @endif
    </section>

    @script
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('show-error', (event) => {
                Toast.fire({
                    icon: 'error',
                    title: event.message
                });
            });
        });
    </script>
    @endscript
</div>