path = "resources/views/store/profile.blade.php"

with open(path, "r", encoding="utf-8") as f:
    lines = f.readlines()

start_idx = None
for i, line in enumerate(lines):
    if "@elseif($order->status === 'delivered')" in line:
        start_idx = i
        break

if start_idx is None:
    print("GAGAL: baris \"@elseif($order->status === 'delivered')\" tidak ditemukan.")
    raise SystemExit

endif_count = 0
end_idx = None
for j in range(start_idx, len(lines)):
    if lines[j].strip() == "@endif":
        endif_count += 1
        if endif_count == 2:
            end_idx = j
            break

if end_idx is None:
    print("GAGAL: penutup @endif kedua tidak ditemukan setelah baris awal.")
    raise SystemExit

new_block = '''                            @elseif($order->status === 'delivered')

                                <div class="bg-green-50 border border-green-200 rounded-2xl p-4 sm:p-5">

                                    <div class="flex items-start gap-3">

                                        <div class="text-xl">
                                            ✓
                                        </div>

                                        <div>

                                            <div class="font-semibold text-green-700">
                                                Pesanan Telah Diterima
                                            </div>

                                            <div class="text-sm text-green-600 mt-1">
                                                Terima kasih telah berbelanja di Zalina Fashion.
                                            </div>

                                        </div>

                                    </div>

                                </div>


                                {{-- ================================================= --}}
                                {{-- RETURN SECTION --}}
                                {{-- ================================================= --}}

                                @if ($order->return_status === 'requested')

                                    <div class="mt-5 bg-white border border-maroon-100 rounded-2xl p-5 sm:p-6 shadow-sm">

                                        <div class="flex items-start gap-3">

                                            <div class="w-10 h-10 rounded-full bg-amber-50 flex items-center justify-center shrink-0 text-xl">
                                                ⏳
                                            </div>

                                            <div class="min-w-0">

                                                <div class="font-serif text-lg text-maroon-900">
                                                    Retur Sedang Ditinjau
                                                </div>

                                                <p class="text-sm text-maroon-500 mt-1 leading-5">
                                                    Pengajuan retur Anda sudah kami terima dan sedang menunggu
                                                    persetujuan admin. Kami akan mengabari Anda begitu ada keputusan.
                                                </p>

                                                <div class="mt-4 bg-maroon-50/60 border border-maroon-100 rounded-xl p-4 space-y-2">

                                                    <div class="flex justify-between gap-3 text-sm">
                                                        <span class="text-maroon-400">Alasan</span>
                                                        <span class="font-semibold text-maroon-900 text-right">{{ $order->return_reason }}</span>
                                                    </div>

                                                    @if ($order->return_customer_note)
                                                        <div class="flex justify-between gap-3 text-sm">
                                                            <span class="text-maroon-400">Catatan</span>
                                                            <span class="font-semibold text-maroon-900 text-right">{{ $order->return_customer_note }}</span>
                                                        </div>
                                                    @endif

                                                    @if ($order->return_image)
                                                        <div class="pt-2">
                                                            <a href="{{ asset('storage/' . $order->return_image) }}"
                                                               target="_blank"
                                                               class="inline-flex items-center gap-1.5 text-xs font-semibold text-maroon-700 underline">
                                                                📎 Lihat foto bukti yang Anda kirim
                                                            </a>
                                                        </div>
                                                    @endif

                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                @elseif ($order->return_status === 'approved')

                                    <div class="mt-5 bg-white border border-green-200 rounded-2xl p-5 sm:p-6 shadow-sm">

                                        <div class="flex items-start gap-3">

                                            <div class="w-10 h-10 rounded-full bg-green-50 flex items-center justify-center shrink-0 text-xl">
                                                ✓
                                            </div>

                                            <div class="min-w-0">

                                                <div class="font-serif text-lg text-green-800">
                                                    Retur Disetujui
                                                </div>

                                                <p class="text-sm text-green-700 mt-1 leading-5">
                                                    Pengajuan retur Anda telah disetujui. Mohon ikuti langkah
                                                    pengembalian barang di bawah ini.
                                                </p>

                                                @if ($order->return_admin_note)
                                                    <div class="mt-3 bg-green-50 border border-green-100 rounded-xl p-3 text-sm text-green-700">
                                                        <span class="font-semibold">Catatan admin:</span>
                                                        {{ $order->return_admin_note }}
                                                    </div>
                                                @endif

                                                <div class="mt-4 bg-maroon-50/60 border border-maroon-100 rounded-xl p-4">

                                                    <div class="font-semibold text-maroon-900 text-sm mb-2">
                                                        📦 Syarat & Cara Pengembalian Barang
                                                    </div>

                                                    <ol class="text-sm text-maroon-600 space-y-1.5 list-decimal list-inside leading-5">
                                                        <li>Kemas produk dalam kondisi asli beserta label dan kemasan.</li>
                                                        <li>Sertakan salinan struk/nomor pesanan <strong>{{ $order->order_number ?? ('#' . $order->id) }}</strong> di dalam paket.</li>
                                                        <li>Kirim ke alamat: <strong>Zalina Fashion, Gresik, Jawa Timur</strong> (detail lengkap dikirim via WhatsApp/email).</li>
                                                        <li>Gunakan jasa kurir yang bisa dilacak, lalu simpan nomor resinya.</li>
                                                        <li>Setelah barang kami terima dan diperiksa, retur akan diselesaikan dan proses refund/penggantian akan diproses.</li>
                                                    </ol>

                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                @elseif ($order->return_status === 'rejected')

                                    <div class="mt-5 bg-white border border-red-200 rounded-2xl p-5 sm:p-6 shadow-sm">

                                        <div class="flex items-start gap-3">

                                            <div class="w-10 h-10 rounded-full bg-red-50 flex items-center justify-center shrink-0 text-xl">
                                                ✕
                                            </div>

                                            <div class="min-w-0">

                                                <div class="font-serif text-lg text-red-700">
                                                    Retur Tidak Disetujui
                                                </div>

                                                <p class="text-sm text-red-600 mt-1 leading-5">
                                                    Mohon maaf, pengajuan retur Anda belum bisa kami proses.
                                                </p>

                                                @if ($order->return_admin_note)
                                                    <div class="mt-3 bg-red-50 border border-red-100 rounded-xl p-3 text-sm text-red-600">
                                                        <span class="font-semibold">Alasan:</span>
                                                        {{ $order->return_admin_note }}
                                                    </div>
                                                @endif

                                            </div>

                                        </div>

                                    </div>

                                @elseif ($order->return_status === 'completed')

                                    <div class="mt-5 bg-white border border-maroon-100 rounded-2xl p-5 sm:p-6 shadow-sm">

                                        <div class="flex items-start gap-3">

                                            <div class="w-10 h-10 rounded-full bg-maroon-50 flex items-center justify-center shrink-0 text-xl">
                                                🎉
                                            </div>

                                            <div class="min-w-0">

                                                <div class="font-serif text-lg text-maroon-900">
                                                    Retur Selesai
                                                </div>

                                                <p class="text-sm text-maroon-500 mt-1 leading-5">
                                                    Proses retur untuk pesanan ini sudah selesai. Terima kasih atas kesabaran Anda.
                                                </p>

                                            </div>

                                        </div>

                                    </div>

                                @elseif ($order->canRequestReturn())

                                    <div class="mt-5 bg-white border border-maroon-100 rounded-2xl p-5 sm:p-6 shadow-sm">

                                        <div class="flex items-start gap-3">

                                            <div class="w-10 h-10 rounded-full bg-maroon-50 flex items-center justify-center shrink-0 text-xl">
                                                🔄
                                            </div>

                                            <div class="min-w-0 flex-1">

                                                <div class="font-serif text-lg text-maroon-900">
                                                    Ajukan Retur Produk
                                                </div>

                                                <p class="text-sm text-maroon-500 mt-1 leading-5">
                                                    Produk bermasalah? Ajukan retur dan jelaskan alasan Anda.
                                                </p>

                                                @if ($errors->any() && old('return_order_id') == $order->id)
                                                    <div class="mt-4 bg-red-50 border border-red-200 rounded-xl p-3 text-sm text-red-600 space-y-1">
                                                        @foreach ($errors->all() as $error)
                                                            <div>{{ $error }}</div>
                                                        @endforeach
                                                    </div>
                                                @endif

                                                <form
                                                    method="POST"
                                                    action="{{ route('order.return.request', $order) }}"
                                                    enctype="multipart/form-data"
                                                    class="mt-4 space-y-4"
                                                    id="returnForm{{ $order->id }}"
                                                >

                                                    @csrf

                                                    <input type="hidden" name="return_order_id" value="{{ $order->id }}">

                                                    <div>
                                                        <label class="block text-sm font-semibold text-maroon-900 mb-1.5">
                                                            Alasan Retur
                                                        </label>
                                                        <select
                                                            name="reason"
                                                            required
                                                            class="w-full rounded-xl border border-maroon-200 px-4 py-3 text-sm text-maroon-900 bg-white focus:outline-none focus:border-maroon-500 focus:ring-1 focus:ring-maroon-500"
                                                        >
                                                            <option value="">Pilih alasan retur</option>
                                                            <option value="Produk rusak">Produk rusak</option>
                                                            <option value="Salah ukuran">Salah ukuran</option>
                                                            <option value="Tidak sesuai deskripsi">Tidak sesuai deskripsi</option>
                                                        </select>
                                                    </div>

                                                    <div>
                                                        <label class="block text-sm font-semibold text-maroon-900 mb-1.5">
                                                            Catatan Tambahan
                                                        </label>
                                                        <textarea
                                                            name="note"
                                                            rows="3"
                                                            placeholder="Jelaskan detail masalah pada produk Anda..."
                                                            class="w-full rounded-xl border border-maroon-200 px-4 py-3 text-sm text-maroon-900 placeholder:text-maroon-300 bg-white focus:outline-none focus:border-maroon-500 focus:ring-1 focus:ring-maroon-500 resize-none"
                                                        ></textarea>
                                                    </div>

                                                    <div>
                                                        <label class="block text-sm font-semibold text-maroon-900 mb-1.5">
                                                            Foto Bukti Produk
                                                        </label>

                                                        <label
                                                            for="returnImage{{ $order->id }}"
                                                            class="flex items-center gap-3 border border-dashed border-maroon-200 rounded-xl px-4 py-3 cursor-pointer hover:border-maroon-400 transition"
                                                        >
                                                            <span class="shrink-0 bg-maroon-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg">
                                                                Pilih Foto
                                                            </span>
                                                            <span
                                                                id="returnFileName{{ $order->id }}"
                                                                class="text-sm text-maroon-400 truncate"
                                                            >
                                                                Belum ada foto dipilih
                                                            </span>
                                                            <input
                                                                type="file"
                                                                name="image"
                                                                id="returnImage{{ $order->id }}"
                                                                accept="image/jpeg,image/jpg,image/png,image/webp"
                                                                capture="environment"
                                                                class="hidden"
                                                            >
                                                        </label>

                                                        <p class="text-xs text-maroon-300 mt-1.5">
                                                            Format JPG, JPEG, PNG, atau WEBP. Maksimal 5 MB.
                                                        </p>

                                                        <p
                                                            id="returnFileError{{ $order->id }}"
                                                            class="hidden text-xs font-semibold text-red-600 mt-1.5"
                                                        >
                                                            Ukuran foto terlalu besar (maks. 5 MB). Pilih foto yang lebih kecil.
                                                        </p>
                                                    </div>

                                                    <button
                                                        type="submit"
                                                        id="returnSubmit{{ $order->id }}"
                                                        class="w-full sm:w-auto bg-maroon-700 hover:bg-maroon-800 text-white px-6 py-3 rounded-xl font-semibold text-sm transition"
                                                    >
                                                        Kirim Pengajuan Retur
                                                    </button>

                                                </form>

                                                <script>
                                                (function () {
                                                    var input = document.getElementById('returnImage{{ $order->id }}');
                                                    var nameLabel = document.getElementById('returnFileName{{ $order->id }}');
                                                    var errorText = document.getElementById('returnFileError{{ $order->id }}');
                                                    var submitBtn = document.getElementById('returnSubmit{{ $order->id }}');
                                                    var maxBytes = 5 * 1024 * 1024;

                                                    if (!input) { return; }

                                                    input.addEventListener('change', function () {
                                                        var file = input.files && input.files[0];

                                                        if (!file) {
                                                            nameLabel.textContent = 'Belum ada foto dipilih';
                                                            errorText.classList.add('hidden');
                                                            submitBtn.disabled = false;
                                                            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                                                            return;
                                                        }

                                                        nameLabel.textContent = file.name;

                                                        if (file.size > maxBytes) {
                                                            errorText.classList.remove('hidden');
                                                            submitBtn.disabled = true;
                                                            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                                                        } else {
                                                            errorText.classList.add('hidden');
                                                            submitBtn.disabled = false;
                                                            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                                                        }
                                                    });
                                                })();
                                                </script>

                                            </div>

                                        </div>

                                    </div>

                                @endif

                            @endif
'''

lines[start_idx:end_idx + 1] = [new_block]

with open(path, "w", encoding="utf-8") as f:
    f.writelines(lines)

print("DONE: section retur di profile.blade.php sudah diredesign (baris " + str(start_idx + 1) + " s/d " + str(end_idx + 1) + ")")
