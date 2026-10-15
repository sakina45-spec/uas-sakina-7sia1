# Paket Perbaikan CBT SIA1

Paket ini berasal dari `app.zip` yang diberikan, kemudian ditambahkan/perbaiki:

- Admin > Bank Soal > Upload Soal CSV.
- Pelajaran pada CSV dibuat otomatis jika belum ada.
- Login siswa menggunakan NIS + password.
- Daftar Sesi Ujian siswa dengan tombol Mulai Ujian.
- Tampilan CBT profesional: timer, nomor soal, status dijawab/belum, navigasi soal, dan konfirmasi selesai.
- Jawaban langsung disimpan sehingga tidak hilang saat pindah soal/reload.
- Urutan soal tetap untuk satu attempt.
- Penilaian otomatis dan rekap per mata pelajaran.
- Histori Pelajaran di sidebar siswa.
- Template CSV tersedia di `docs/template-soal.csv`.

## Cara memasang

1. Extract ZIP ini.
2. Salin folder `app` ke root project Laravel kamu dan pilih replace/merge.
3. Salin folder `resources` ke root project Laravel kamu.
4. Folder `docs` hanya berisi panduan/template CSV.
5. Jalankan:

```powershell
php artisan optimize:clear
```

6. Pastikan database project kamu sudah berisi tabel `users`, `students`, `subjects`, `questions`, `answers`, `exams`, `exam_attempts`, `exam_attempt_answers`, dan `exam_attempt_subjects`. Paket ini tidak menambah kolom database baru.

## Format CSV

Header wajib:

```csv
subject,question,score,option_a,option_b,option_c,option_d,correct_answer
```

`correct_answer` harus salah satu dari `A`, `B`, `C`, atau `D`. Satu baris = satu soal.

Contoh lengkap ada di `docs/template-soal.csv`.
