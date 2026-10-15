<x-filament-panels::page>

    @php
        // Sisa waktu dihitung dari waktu mulai ujian, jadi tidak reset saat halaman di-refresh
        $remainingSeconds = 0;

        if (! $examFinished && $attempt?->started_at) {
            $elapsed = (int) \Illuminate\Support\Carbon::parse($attempt->started_at)->diffInSeconds(now());
            $remainingSeconds = max(0, ((int) $record->duration * 60) - $elapsed);
        }

        $student = auth()->user()?->student;
    @endphp

    {{-- CSS khusus halaman ujian. Ditulis manual (bukan class Tailwind) karena Tailwind
         bawaan Filament hanya memuat class miliknya sendiri, sehingga class baru tidak terpakai. --}}
    <style>
        .cbt {
            --cbt-card: #ffffff;
            --cbt-soft: var(--gray-50);
            --cbt-border: var(--gray-200);
            --cbt-text: var(--gray-900);
            --cbt-muted: var(--gray-500);
            --cbt-primary: var(--primary-600);
            --cbt-primary-soft: color-mix(in srgb, var(--primary-600) 10%, transparent);
            --cbt-success: #16a34a;
            --cbt-success-soft: color-mix(in srgb, #16a34a 12%, transparent);
            --cbt-danger: #dc2626;
            --cbt-danger-soft: color-mix(in srgb, #dc2626 12%, transparent);
            color: var(--cbt-text);
            width: 100%;
            max-width: 80rem;
            margin: 0 auto;
        }
        .dark .cbt {
            --cbt-card: var(--gray-900);
            --cbt-soft: var(--gray-800);
            --cbt-border: var(--gray-700);
            --cbt-text: #ffffff;
            --cbt-muted: var(--gray-400);
            --cbt-primary: var(--primary-500);
            --cbt-primary-soft: color-mix(in srgb, var(--primary-500) 18%, transparent);
            --cbt-success: #4ade80;
            --cbt-danger: #f87171;
        }

        .cbt * { box-sizing: border-box; }

        .cbt-card {
            background: var(--cbt-card);
            border: 1px solid var(--cbt-border);
            border-radius: 0.875rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .cbt-icon { width: 1.25rem; height: 1.25rem; flex-shrink: 0; }
        .cbt-icon-sm { width: 1rem; height: 1rem; flex-shrink: 0; }
        .cbt-icon-xl { width: 2.5rem; height: 2.5rem; flex-shrink: 0; }

        /* ---------- Header ---------- */
        .cbt-header {
            display: flex; flex-wrap: wrap; align-items: center;
            justify-content: space-between; gap: 1rem;
            padding: 1rem 1.25rem; margin-bottom: 1rem;
        }
        .cbt-header-left { display: flex; align-items: center; gap: 0.75rem; min-width: 0; }
        .cbt-badge-icon {
            display: flex; align-items: center; justify-content: center;
            width: 2.75rem; height: 2.75rem; border-radius: 0.75rem;
            background: var(--cbt-primary); color: #fff; flex-shrink: 0;
        }
        .cbt-label { font-size: 0.7rem; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase; color: var(--cbt-muted); }
        .cbt-name { font-size: 1rem; font-weight: 700; line-height: 1.3; }
        .cbt-meta { font-size: 0.8rem; color: var(--cbt-muted); }

        .cbt-timer {
            display: flex; align-items: center; gap: 0.75rem;
            padding: 0.6rem 1.1rem; border-radius: 0.75rem;
            border: 1px solid var(--cbt-border); background: var(--cbt-soft);
        }
        .cbt-timer .cbt-icon { color: var(--cbt-primary); }
        .cbt-timer-value { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 1.25rem; font-weight: 700; line-height: 1.2; }
        .cbt-timer.is-warning { border-color: var(--cbt-danger); background: var(--cbt-danger-soft); color: var(--cbt-danger); }
        .cbt-timer.is-warning .cbt-icon { color: var(--cbt-danger); }

        /* ---------- Progress ---------- */
        .cbt-progress { padding: 0.9rem 1.25rem; margin-bottom: 1rem; }
        .cbt-progress-row { display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 0.5rem; }
        .cbt-progress-row span:first-child { color: var(--cbt-muted); font-weight: 500; }
        .cbt-progress-row span:last-child { color: var(--cbt-primary); font-weight: 700; }
        .cbt-bar { height: 0.5rem; border-radius: 999px; background: var(--cbt-soft); border: 1px solid var(--cbt-border); overflow: hidden; }
        .cbt-bar > div { height: 100%; border-radius: 999px; background: var(--cbt-primary); transition: width 0.3s ease; }

        /* ---------- Layout ---------- */
        .cbt-layout { display: grid; grid-template-columns: 1fr; gap: 1rem; align-items: start; }
        @media (min-width: 1024px) {
            .cbt-layout { grid-template-columns: 17rem 1fr; }
            .cbt-side { position: sticky; top: 1rem; }
        }

        /* ---------- Sidebar nomor soal ---------- */
        .cbt-side { padding: 1.25rem; }
        .cbt-side h2 { font-size: 1rem; font-weight: 700; margin: 0; }
        .cbt-side-head { padding-bottom: 0.9rem; border-bottom: 1px solid var(--cbt-border); margin-bottom: 1rem; }
        .cbt-numbers { display: grid; grid-template-columns: repeat(5, 1fr); gap: 0.5rem; }
        .cbt-num {
            position: relative; height: 2.5rem; border-radius: 0.5rem;
            border: 1px solid var(--cbt-border); background: var(--cbt-soft);
            color: var(--cbt-text); font-size: 0.875rem; font-weight: 700;
            cursor: pointer; transition: all 0.15s ease;
        }
        .cbt-num:hover { border-color: var(--cbt-primary); transform: translateY(-1px); }
        .cbt-num.is-answered { background: var(--cbt-success-soft); border-color: var(--cbt-success); color: var(--cbt-success); }
        .cbt-num.is-current { background: var(--cbt-primary); border-color: var(--cbt-primary); color: #fff; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2); }

        .cbt-legend { margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid var(--cbt-border); display: grid; gap: 0.6rem; }
        .cbt-legend div { display: flex; align-items: center; gap: 0.5rem; font-size: 0.78rem; color: var(--cbt-muted); }
        .cbt-dot { width: 0.8rem; height: 0.8rem; border-radius: 0.25rem; border: 1px solid var(--cbt-border); }
        .cbt-dot.current { background: var(--cbt-primary); border-color: var(--cbt-primary); }
        .cbt-dot.answered { background: var(--cbt-success); border-color: var(--cbt-success); }
        .cbt-dot.empty { background: var(--cbt-soft); }

        .cbt-summary { margin-top: 1.25rem; display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; }
        .cbt-summary > div { border-radius: 0.6rem; padding: 0.6rem; text-align: center; background: var(--cbt-soft); }
        .cbt-summary > div.ok { background: var(--cbt-success-soft); }
        .cbt-summary small { display: block; font-size: 0.7rem; color: var(--cbt-muted); }
        .cbt-summary strong { font-size: 1.25rem; font-weight: 700; }
        .cbt-summary .ok strong { color: var(--cbt-success); }

        /* ---------- Area soal ---------- */
        .cbt-qhead { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; padding: 1.1rem 1.5rem; border-bottom: 1px solid var(--cbt-border); }
        .cbt-qtags { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem; font-size: 0.8rem; color: var(--cbt-muted); }
        .cbt-tag { background: var(--cbt-primary-soft); color: var(--cbt-primary); padding: 0.25rem 0.65rem; border-radius: 0.4rem; font-weight: 600; font-size: 0.78rem; }
        .cbt-points { background: var(--cbt-soft); border: 1px solid var(--cbt-border); padding: 0.3rem 0.7rem; border-radius: 0.5rem; font-size: 0.78rem; font-weight: 600; white-space: nowrap; }

        .cbt-qbody { padding: 1.5rem; }
        .cbt-question { font-size: 1.2rem; font-weight: 600; line-height: 1.7; margin-bottom: 1.5rem; }
        .cbt-question img { max-width: 100%; height: auto; border-radius: 0.5rem; }
        .cbt-question p { margin: 0 0 0.5rem; }
        .cbt-hint { font-size: 0.85rem; color: var(--cbt-muted); margin-bottom: 0.9rem; }

        .cbt-options { display: grid; gap: 0.75rem; }
        .cbt-option {
            display: flex; align-items: center; gap: 0.9rem; width: 100%; text-align: left;
            padding: 0.9rem 1rem; border-radius: 0.75rem;
            border: 2px solid var(--cbt-border); background: var(--cbt-card); color: var(--cbt-text);
            cursor: pointer; transition: all 0.15s ease; font: inherit;
        }
        .cbt-option:hover { border-color: var(--cbt-primary); background: var(--cbt-soft); }
        .cbt-option.is-selected { border-color: var(--cbt-primary); background: var(--cbt-primary-soft); }
        .cbt-letter {
            display: flex; align-items: center; justify-content: center;
            width: 2.25rem; height: 2.25rem; border-radius: 0.5rem; flex-shrink: 0;
            background: var(--cbt-soft); border: 1px solid var(--cbt-border);
            font-size: 0.875rem; font-weight: 700;
        }
        .cbt-option.is-selected .cbt-letter { background: var(--cbt-primary); border-color: var(--cbt-primary); color: #fff; }
        .cbt-option-text { flex: 1; font-size: 0.95rem; font-weight: 500; line-height: 1.5; }
        .cbt-check { color: var(--cbt-primary); }

        /* ---------- Navigasi bawah ---------- */
        .cbt-foot {
            display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;
            padding: 1rem 1.5rem; border-top: 1px solid var(--cbt-border); background: var(--cbt-soft);
            border-radius: 0 0 0.875rem 0.875rem;
        }
        .cbt-pos { text-align: center; font-size: 0.8rem; color: var(--cbt-muted); }
        .cbt-pos strong { display: block; color: var(--cbt-text); font-size: 0.95rem; }
        .cbt-btn {
            display: inline-flex; align-items: center; gap: 0.5rem; justify-content: center;
            padding: 0.65rem 1.2rem; border-radius: 0.6rem; font-size: 0.875rem; font-weight: 600;
            border: 1px solid transparent; cursor: pointer; transition: all 0.15s ease; text-decoration: none; font-family: inherit;
        }
        .cbt-btn-primary { background: var(--cbt-primary); color: #fff; }
        .cbt-btn-primary:hover { filter: brightness(1.1); }
        .cbt-btn-success { background: #16a34a; color: #fff; }
        .cbt-btn-success:hover { background: #15803d; }
        .cbt-btn-outline { background: var(--cbt-card); border-color: var(--cbt-border); color: var(--cbt-text); }
        .cbt-btn-outline:hover { background: var(--cbt-soft); }
        .cbt-btn:disabled { opacity: 0.4; cursor: not-allowed; }

        .cbt-empty { padding: 3rem 1.5rem; text-align: center; }
        .cbt-empty .cbt-icon-xl { color: var(--cbt-danger); margin: 0 auto 0.75rem; display: block; }

        @media (max-width: 640px) {
            .cbt-pos { display: none; }
            .cbt-qbody, .cbt-qhead, .cbt-foot { padding-left: 1rem; padding-right: 1rem; }
            .cbt-question { font-size: 1.05rem; }
        }

        /* ---------- Hasil ujian ---------- */
        .cbt-result { max-width: 40rem; margin: 1.5rem auto; overflow: hidden; }
        .cbt-result-top { background: var(--cbt-primary); color: #fff; text-align: center; padding: 2rem 1.5rem; }
        .cbt-result-top h1 { font-size: 1.75rem; font-weight: 800; margin: 0.75rem 0 0.25rem; }
        .cbt-result-top p { margin: 0; opacity: 0.9; font-size: 0.9rem; }
        .cbt-result-icon { width: 4.5rem; height: 4.5rem; margin: 0 auto; border-radius: 999px; background: rgba(255, 255, 255, 0.2); display: flex; align-items: center; justify-content: center; }
        .cbt-result-body { padding: 1.75rem; }
        .cbt-score { text-align: center; }
        .cbt-score small { font-size: 0.75rem; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: var(--cbt-muted); }
        .cbt-score-value { font-size: 4.5rem; font-weight: 900; line-height: 1.1; color: var(--cbt-primary); margin: 0.25rem 0; }
        .cbt-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem; margin-top: 1.5rem; }
        .cbt-stat { border-radius: 0.75rem; padding: 0.9rem; text-align: center; border: 1px solid var(--cbt-border); background: var(--cbt-soft); }
        .cbt-stat small { display: block; font-size: 0.75rem; color: var(--cbt-muted); }
        .cbt-stat strong { font-size: 1.5rem; font-weight: 700; }
        .cbt-stat.ok { background: var(--cbt-success-soft); border-color: var(--cbt-success); }
        .cbt-stat.ok strong { color: var(--cbt-success); }
        .cbt-stat.bad { background: var(--cbt-danger-soft); border-color: var(--cbt-danger); }
        .cbt-stat.bad strong { color: var(--cbt-danger); }
        .cbt-status { margin-top: 1.25rem; border-radius: 0.75rem; padding: 1rem; text-align: center; border: 1px solid; }
        .cbt-status.ok { background: var(--cbt-success-soft); border-color: var(--cbt-success); color: var(--cbt-success); }
        .cbt-status.bad { background: var(--cbt-danger-soft); border-color: var(--cbt-danger); color: var(--cbt-danger); }
        .cbt-status strong { display: block; font-size: 1rem; }
        .cbt-status span { font-size: 0.85rem; }
        .cbt-back { margin-top: 1.75rem; text-align: center; }
    </style>

    {{-- =========================================================
        HASIL UJIAN
    ========================================================== --}}
    @if ($examFinished)

        <div class="cbt">
            <div class="cbt-card cbt-result">

                <div class="cbt-result-top">
                    <div class="cbt-result-icon">
                        <x-filament::icon icon="heroicon-o-check" class="cbt-icon-xl" />
                    </div>
                    <h1>Ujian Selesai</h1>
                    <p>Jawaban kamu telah berhasil disimpan.</p>
                </div>

                <div class="cbt-result-body">

                    <div class="cbt-score">
                        <small>Nilai Akhir</small>
                        <div class="cbt-score-value">{{ number_format((float) $attempt->score, 0) }}</div>
                        <div class="cbt-meta">
                            KKM: <strong>{{ number_format((float) $record->threshold, 0) }}</strong>
                        </div>
                    </div>

                    <div class="cbt-stats">
                        <div class="cbt-stat ok">
                            <small>Benar</small>
                            <strong>{{ $attempt->correct_answers }}</strong>
                        </div>
                        <div class="cbt-stat bad">
                            <small>Salah</small>
                            <strong>{{ $attempt->wrong_answers }}</strong>
                        </div>
                        <div class="cbt-stat">
                            <small>Total</small>
                            <strong>{{ $attempt->total_questions }}</strong>
                        </div>
                    </div>

                    @if ($this->passed)
                        <div class="cbt-status ok">
                            <strong>🎉 Selamat, kamu LULUS!</strong>
                            <span>Nilai kamu telah mencapai KKM.</span>
                        </div>
                    @else
                        <div class="cbt-status bad">
                            <strong>Belum Lulus</strong>
                            <span>Nilai kamu belum mencapai KKM.</span>
                        </div>
                    @endif

                    <div class="cbt-back">
                        <a href="{{ url('/ujian/exams') }}" class="cbt-btn cbt-btn-primary">
                            <x-filament::icon icon="heroicon-o-arrow-left" class="cbt-icon-sm" />
                            Kembali ke Daftar Ujian
                        </a>
                    </div>

                </div>
            </div>
        </div>

    {{-- =========================================================
        HALAMAN UJIAN
    ========================================================== --}}
    @else

        <div
            class="cbt"
            x-data="{
                remaining: {{ $remainingSeconds }},
                timer: null,

                formatTime() {
                    const h = Math.floor(this.remaining / 3600);
                    const m = Math.floor((this.remaining % 3600) / 60);
                    const s = this.remaining % 60;

                    return String(h).padStart(2, '0') + ':' +
                           String(m).padStart(2, '0') + ':' +
                           String(s).padStart(2, '0');
                },

                startTimer() {
                    this.timer = setInterval(() => {
                        if (this.remaining > 0) {
                            this.remaining--;
                        } else {
                            clearInterval(this.timer);
                            $wire.finishExam();
                        }
                    }, 1000);
                },

                destroy() {
                    clearInterval(this.timer);
                }
            }"
            x-init="startTimer()"
        >

            {{-- HEADER --}}
            <div class="cbt-card cbt-header">

                <div class="cbt-header-left">
                    <div class="cbt-badge-icon">
                        <x-filament::icon icon="heroicon-o-academic-cap" class="cbt-icon" />
                    </div>
                    <div>
                        <div class="cbt-label">Computer Based Test</div>
                        <div class="cbt-name">{{ $student?->name ?? auth()->user()?->name }}</div>
                        <div class="cbt-meta">
                            @if ($student?->nis) NIS {{ $student->nis }} &middot; @endif
                            Durasi {{ $record->duration }} menit &middot; KKM {{ number_format((float) $record->threshold, 0) }}
                        </div>
                    </div>
                </div>

                <div class="cbt-timer" :class="{ 'is-warning': remaining <= 300 }">
                    <x-filament::icon icon="heroicon-o-clock" class="cbt-icon" />
                    <div>
                        <div class="cbt-label">Waktu Tersisa</div>
                        <div class="cbt-timer-value" x-text="formatTime()"></div>
                    </div>
                </div>

            </div>

            {{-- PROGRESS --}}
            <div class="cbt-card cbt-progress">
                <div class="cbt-progress-row">
                    <span>Progress Pengerjaan</span>
                    <span>{{ $this->answeredCount }} / {{ $this->totalQuestions }} soal</span>
                </div>
                <div class="cbt-bar">
                    <div style="width: {{ $this->totalQuestions > 0 ? ($this->answeredCount / $this->totalQuestions) * 100 : 0 }}%"></div>
                </div>
            </div>

            <div class="cbt-layout">

                {{-- SIDEBAR NOMOR SOAL --}}
                <aside class="cbt-card cbt-side">

                    <div class="cbt-side-head">
                        <h2>Daftar Soal</h2>
                        <div class="cbt-meta">Pilih nomor soal</div>
                    </div>

                    <div class="cbt-numbers">
                        @foreach ($questions as $index => $question)
                            @php
                                $isCurrent = $index === $currentQuestionIndex;
                                $isAnswered = isset($answers[$question['id']]);
                            @endphp

                            <button
                                type="button"
                                wire:click="goToQuestion({{ $index }})"
                                title="Soal {{ $index + 1 }}"
                                class="cbt-num {{ $isCurrent ? 'is-current' : ($isAnswered ? 'is-answered' : '') }}"
                            >
                                {{ $index + 1 }}
                            </button>
                        @endforeach
                    </div>

                    <div class="cbt-legend">
                        <div><span class="cbt-dot current"></span> Soal sedang dibuka</div>
                        <div><span class="cbt-dot answered"></span> Sudah dijawab</div>
                        <div><span class="cbt-dot empty"></span> Belum dijawab</div>
                    </div>

                    <div class="cbt-summary">
                        <div class="ok">
                            <small>Dijawab</small>
                            <strong>{{ $this->answeredCount }}</strong>
                        </div>
                        <div>
                            <small>Belum</small>
                            <strong>{{ $this->remainingQuestions }}</strong>
                        </div>
                    </div>

                </aside>

                {{-- AREA SOAL --}}
                <main>

                    @if ($this->currentQuestion)

                        @php
                            $current = $this->currentQuestion;
                            $payload = (string) $current['payload'];
                            // Soal dari editor teks berformat berisi HTML; soal polos tetap aman lewat e()
                            $isHtml = $payload !== strip_tags($payload);
                        @endphp

                        <div class="cbt-card">

                            <div class="cbt-qhead">
                                <div class="cbt-qtags">
                                    <span class="cbt-tag">{{ $current['subject_name'] }}</span>
                                    <span>Soal {{ $currentQuestionIndex + 1 }} dari {{ $this->totalQuestions }}</span>
                                </div>
                                <div class="cbt-points">{{ $current['score'] }} poin</div>
                            </div>

                            <div class="cbt-qbody">

                                <div class="cbt-question">
                                    @if ($isHtml)
                                        {!! $payload !!}
                                    @else
                                        {!! nl2br(e($payload)) !!}
                                    @endif
                                </div>

                                <p class="cbt-hint">Pilih salah satu jawaban yang paling tepat.</p>

                                <div class="cbt-options">
                                    @foreach ($current['answers'] as $answer)
                                        @php
                                            $isSelected = ($answers[$current['id']] ?? null) == $answer['id'];
                                        @endphp

                                        <button
                                            type="button"
                                            wire:click="selectAnswer({{ $answer['id'] }})"
                                            class="cbt-option {{ $isSelected ? 'is-selected' : '' }}"
                                        >
                                            <span class="cbt-letter">{{ chr(65 + $loop->index) }}</span>
                                            <span class="cbt-option-text">{{ $answer['text'] }}</span>
                                            @if ($isSelected)
                                                <x-filament::icon icon="heroicon-s-check-circle" class="cbt-icon cbt-check" />
                                            @endif
                                        </button>
                                    @endforeach
                                </div>

                            </div>

                            <div class="cbt-foot">

                                <button
                                    type="button"
                                    wire:click="previousQuestion"
                                    @disabled($currentQuestionIndex === 0)
                                    class="cbt-btn cbt-btn-outline"
                                >
                                    <x-filament::icon icon="heroicon-o-chevron-left" class="cbt-icon-sm" />
                                    Sebelumnya
                                </button>

                                <div class="cbt-pos">
                                    Nomor Soal
                                    <strong>{{ $currentQuestionIndex + 1 }} / {{ $this->totalQuestions }}</strong>
                                </div>

                                @if ($currentQuestionIndex < $this->totalQuestions - 1)
                                    <button type="button" wire:click="nextQuestion" class="cbt-btn cbt-btn-primary">
                                        Berikutnya
                                        <x-filament::icon icon="heroicon-o-chevron-right" class="cbt-icon-sm" />
                                    </button>
                                @else
                                    <button
                                        type="button"
                                        wire:click="finishExam"
                                        wire:confirm="Apakah kamu yakin ingin menyelesaikan ujian? Pastikan semua jawaban sudah diperiksa."
                                        class="cbt-btn cbt-btn-success"
                                    >
                                        <x-filament::icon icon="heroicon-o-check" class="cbt-icon-sm" />
                                        Selesai Ujian
                                    </button>
                                @endif

                            </div>

                        </div>

                    @else

                        <div class="cbt-card cbt-empty">
                            <x-filament::icon icon="heroicon-o-exclamation-triangle" class="cbt-icon-xl" />
                            <h2 class="cbt-name">Soal tidak ditemukan</h2>
                            <p class="cbt-meta">Silakan kembali ke daftar ujian.</p>
                        </div>

                    @endif

                </main>

            </div>

        </div>

    @endif

</x-filament-panels::page>