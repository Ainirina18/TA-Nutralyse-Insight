<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeminiService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl =
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-3-flash-preview:generateContent?key='
            . env('GEMINI_API_KEY');
    }

    private function sendPrompt($prompt)
    {
        $response = Http::timeout(120)->post(
            $this->baseUrl,
            [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => $prompt
                            ]
                        ]
                    ]
                ]
            ]
        );

        return $response->json();
    }

    public function generateMonthlyEvaluation($analysis)
    {
        $prompt = "

        Kamu adalah AI nutrition assistant untuk balita dan orang tua balita.

        Analisis data nutrisi berikut secara naratif dan natural. pastikan detail, jelas, dan sesuai data

        Target AKG:
        - Energi: {$analysis['needs']['energy']}
        - Protein: {$analysis['needs']['protein']}
        - Lemak: {$analysis['needs']['fat']}

        Persentase pencapaian:
        - Energi: {$analysis['percentage']['energy']}%
        - Protein: {$analysis['percentage']['protein']}%
        - Lemak: {$analysis['percentage']['fat']}%

        Anak memiliki alergi terhadap: {$analysis['allergy']} Pastikan rekomendasi makanan tidak mengandung bahan tersebut.


        ATURAN:
        - Jangan gunakan bullet point
        - Jangan gunakan sapaan
        - Jangan gunakan pembuka atau penutup
        - Tulis tiap bagian dalam bentuk paragraf singkat
        - Maksimal 7 kalimat tiap bagian, minimal 3 kalimat
        - Gunakan bahasa natutal, hangat, mudah dimengerti, menyenangkan, dan suportif untuk orang tua
        - Tambahkan penjelasan data yang diperoleh, dan dampak pada anak

        Format WAJIB:
        ===PERJALANAN_NUTRISI===
        isi paragraf
        ===EVALUASI_CELAH===
        isi paragraf
        ===STRATEGI_MENU===
        isi paragraf
        ===MOTIVASI_PARENT===
        isi paragraf
        ";

        return $this->sendPrompt($prompt);
    }

    public function generateWeeklyWarning($weeklyData)
    {
        $prompt = "
        Kamu adalah AI nutrition assistant untuk balita.

        ATURAN:

        - Jangan gunakan bullet point
        - Jangan gunakan sapaan
        - Jangan gunakan pembuka atau penutup

        Berikut data nutrisi minggu ini:

        Energi: {$weeklyData['energy_percentage']}%
        Protein: {$weeklyData['protein_percentage']}%
        Lemak: {$weeklyData['fat_percentage']}%

        Buat early warning singkat maksimal 2 kalimat
        dengan bahasa hangat, sederhana, pendek, dan jelas untuk orang tua.
        ";

        return $this->sendPrompt($prompt);
    }

    public function generateMonthlyMission($analysis)
    {
        $prompt = "

        Kamu adalah AI nutrition assistant untuk balita.

        Berdasarkan data nutrisi bulanan berikut:

        Energi: {$analysis['percentage']['energy']}%
        Protein: {$analysis['percentage']['protein']}%
        Lemak: {$analysis['percentage']['fat']}%

       Anak memiliki alergi terhadap: {$analysis['allergy']} Pastikan rekomendasi makanan tidak mengandung bahan tersebut.

        Buat misi kecil yang menarik untuk orang tua
        agar membantu meningkatkan nutrisi anak bulan depan.

        ATURAN:
       
        - Gunakan bahasa hangat, lucu, dan suportif untuk orang tua.
        - maksimal 3 kalimat
        - Fokus pada snack, menu kecil, atau kebiasaan makan sederhana
        - Judul harus singkat dan menarik
        - Judul selalu awali dengan Misi Rahasia:
        - Di akhir kalimat selalu ingatkan untuk kembali menu rekomendasi makanan pada aplikasi nutralyse

        Format WAJIB:

        ===MISSION_TITLE===
        isi judul

        ===MISSION_CONTENT===
        isi konten

        ";

        return $this->sendPrompt($prompt);
    }

}