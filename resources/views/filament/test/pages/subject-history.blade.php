<x-filament-panels::page>
    <div style="
        margin-bottom: 20px;
        padding: 20px 24px;
        border-radius: 16px;
        background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
        color: #ffffff;
        box-shadow: 0 4px 14px rgba(79, 70, 229, 0.25);
    ">
        <div style="display: flex; align-items: center; gap: 16px;">
            <div style="
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                width: 48px;
                height: 48px;
                border-radius: 12px;
                background: rgba(255, 255, 255, 0.18);
            ">
                <x-heroicon-o-academic-cap style="width: 28px; height: 28px;" />
            </div>

            <div>
                <h2 style="margin: 0; font-size: 20px; font-weight: 700; line-height: 1.3;">
                    Histori Pelajaran
                </h2>
                <p style="margin: 4px 0 0; font-size: 14px; color: rgba(255, 255, 255, 0.85);">
                    Lihat perkembangan nilai dan hasil belajar kamu dari setiap ujian.
                </p>
            </div>
        </div>
    </div>

    {{ $this->table }}
</x-filament-panels::page>