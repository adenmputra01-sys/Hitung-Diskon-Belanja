<?php
// Menentukan persentase diskon berdasarkan subtotal
function tentukanDiskon(float $subtotal): float
{
    if ($subtotal >= 500000) {
        return 0.20;
    } elseif ($subtotal >= 250000) {
        return 0.10;
    } elseif ($subtotal >= 100000) {
        return 0.05;
    }
    return 0.00;
}

function rupiah(float $angka): string
{
    return "Rp " . number_format($angka, 0, ",", ".");
}

function e($teks): string
{
    return htmlspecialchars((string)$teks, ENT_QUOTES, 'UTF-8');
}

// Inisialisasi variabel
$namaProduk       = "";
$hargaInput       = "";
$jumlahInput      = "1";
$pesanError       = "";
$rincian          = [];
$persentaseDiskon = 0.0;
$nominalDiskon    = 0.0;
$hasilDitampilkan = false;

// Tingkat diskon (untuk ditampilkan sebagai penanda)
$tingkatDiskon = [
    ["persen" => 0.05, "syarat" => "Rp 100 rb"],
    ["persen" => 0.10, "syarat" => "Rp 250 rb"],
    ["persen" => 0.20, "syarat" => "Rp 500 rb"],
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $namaProduk  = trim($_POST["nama_produk"] ?? "");
    $hargaInput  = trim($_POST["harga"] ?? "");
    $jumlahInput = trim($_POST["jumlah"] ?? "");

    $valid =
        $namaProduk !== "" &&
        is_numeric($hargaInput) &&
        is_numeric($jumlahInput) &&
        (float)$hargaInput > 0 &&
        (float)$hargaInput <= 1000000000 &&
        (float)$jumlahInput >= 1 &&
        (float)$jumlahInput <= 10000 &&
        floor((float)$jumlahInput) == (float)$jumlahInput;

    if ($valid) {
        $harga  = (float)$hargaInput;
        $jumlah = (int)$jumlahInput;

        // Hitung subtotal, diskon, dan total bayar
        $subtotal         = $harga * $jumlah;
        $persentaseDiskon = tentukanDiskon($subtotal);
        $nominalDiskon    = round($subtotal * $persentaseDiskon);
        $totalBayar       = $subtotal - $nominalDiskon;

        // Data untuk ditampilkan dengan perulangan
        $rincian = [
            "Nama Produk"    => $namaProduk,
            "Harga Satuan"   => rupiah($harga),
            "Jumlah Barang"  => $jumlah . " buah",
            "Subtotal"       => rupiah($subtotal),
            "Diskon"         => round($persentaseDiskon * 100) . "%",
            "Potongan Harga" => "- " . rupiah($nominalDiskon),
            "Total Bayar"    => rupiah($totalBayar),
        ];

        $hasilDitampilkan = true;
    } else {
        $pesanError = "Isi nama produk, harga lebih dari 0, dan jumlah berupa bilangan bulat 1–10.000.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hitung Diskon Belanja</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --ink: #1c1b3a;
            --muted: #6a6987;
            --indigo: #3a3dbd;
            --indigo-dark: #2b2d96;
            --marigold: #ffc233;
            --marigold-dark: #f2ab00;
            --field: #f1f2ff;
            --line: #d9daf0;
            --paper: #fffef9;
            --green: #0f8a5f;
            --danger: #b42318;
            --danger-bg: #fff1ef;
        }

        * { box-sizing: border-box; }

        html { -webkit-text-size-adjust: 100%; }

        body {
            margin: 0;
            min-height: 100vh;
            padding: clamp(16px, 5vw, 48px) 16px;
            font-family: "Plus Jakarta Sans", system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
            color: var(--ink);
            line-height: 1.5;
            background-color: var(--indigo);
            background-image: radial-gradient(rgba(255, 255, 255, .13) 1.5px, transparent 1.5px);
            background-size: 22px 22px;
        }

        .container {
            width: 100%;
            max-width: 520px;
            margin: 0 auto;
        }

        /* ---------- Kartu form ---------- */
        .kartu {
            background: #fff;
            border-radius: 22px;
            padding: clamp(20px, 5vw, 32px);
            box-shadow: 0 18px 40px rgba(18, 19, 80, .35);
        }

        .judul {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 6px;
        }

        .ikon {
            flex: none;
            display: grid;
            place-items: center;
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: var(--marigold);
            color: var(--ink);
            transform: rotate(-6deg);
        }

        h1 {
            margin: 0;
            font-size: clamp(1.35rem, 5vw, 1.7rem);
            font-weight: 800;
            letter-spacing: -.02em;
            line-height: 1.15;
        }

        .intro {
            margin: 8px 0 0;
            color: var(--muted);
            font-size: .95rem;
        }

        /* ---------- Tingkat diskon ---------- */
        .tingkat {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin: 22px 0 4px;
        }

        .chip {
            padding: 10px 6px;
            border: 2px solid var(--line);
            border-radius: 14px;
            text-align: center;
            transition: background .2s, border-color .2s, transform .2s;
        }

        .chip strong {
            display: block;
            font-size: 1.2rem;
            font-weight: 800;
            line-height: 1.2;
        }

        .chip span {
            display: block;
            font-size: .75rem;
            color: var(--muted);
        }

        .chip.aktif {
            background: var(--marigold);
            border-color: var(--marigold);
            transform: translateY(-2px);
        }

        .chip.aktif span { color: var(--ink); }

        /* ---------- Form ---------- */
        label {
            display: block;
            margin: 20px 0 8px;
            font-weight: 700;
            font-size: .9rem;
        }

        .field {
            display: flex;
            align-items: stretch;
            background: var(--field);
            border: 2px solid transparent;
            border-radius: 12px;
            transition: border-color .15s, box-shadow .15s, background .15s;
        }

        .field:focus-within {
            background: #fff;
            border-color: var(--indigo);
            box-shadow: 0 0 0 4px rgba(58, 61, 189, .15);
        }

        .field .awalan,
        .field .akhiran {
            display: flex;
            align-items: center;
            padding: 0 14px;
            font-weight: 700;
            font-size: .9rem;
            color: var(--muted);
        }

        .field .awalan { padding-right: 0; }

        input {
            flex: 1;
            min-width: 0;
            width: 100%;
            padding: 14px;
            border: 0;
            background: transparent;
            font: inherit;
            font-size: 16px; /* mencegah zoom otomatis di iPhone */
            font-weight: 600;
            color: var(--ink);
            outline: none;
        }

        input::placeholder {
            color: #a5a4c2;
            font-weight: 500;
        }

        button {
            width: 100%;
            margin-top: 26px;
            padding: 15px;
            border: 0;
            border-radius: 12px;
            background: var(--marigold);
            color: var(--ink);
            font: inherit;
            font-size: 1rem;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 4px 0 var(--marigold-dark);
            transition: transform .1s, box-shadow .1s, background .15s;
        }

        button:hover { background: #ffcb52; }

        button:active {
            transform: translateY(3px);
            box-shadow: 0 1px 0 var(--marigold-dark);
        }

        button:focus-visible {
            outline: 3px solid var(--indigo);
            outline-offset: 3px;
        }

        .error {
            margin: 20px 0 0;
            padding: 12px 14px;
            border-left: 4px solid var(--danger);
            border-radius: 8px;
            background: var(--danger-bg);
            color: var(--danger);
            font-size: .92rem;
            font-weight: 500;
        }

        /* ---------- Struk hasil ---------- */
        .struk {
            position: relative;
            margin: 28px 8px 0;
            padding: 24px 22px 30px;
            background: var(--paper);
            color: var(--ink);
            filter: drop-shadow(0 10px 18px rgba(18, 19, 80, .3));
            animation: muncul .45s ease-out;
        }

        /* tepi bergerigi di bawah struk */
        .struk::after {
            content: "";
            position: absolute;
            left: 0;
            right: 0;
            bottom: -10px;
            height: 10px;
            background:
                linear-gradient(-45deg, transparent 7px, var(--paper) 0),
                linear-gradient(45deg, transparent 7px, var(--paper) 0);
            background-size: 14px 14px;
            background-position: left bottom;
            transform: scaleY(-1);
        }

        .struk h2 {
            margin: 0 0 4px;
            text-align: center;
            font-size: 1.05rem;
            font-weight: 800;
            letter-spacing: .02em;
        }

        .struk .sub {
            margin: 0 0 14px;
            padding-bottom: 14px;
            text-align: center;
            font-size: .8rem;
            color: var(--muted);
            border-bottom: 2px dashed var(--line);
        }

        .baris {
            display: flex;
            align-items: baseline;
            gap: 8px;
            padding: 7px 0;
            font-variant-numeric: tabular-nums;
        }

        .baris .label { color: var(--muted); }

        .baris .garis {
            flex: 1;
            min-width: 12px;
            border-bottom: 2px dotted var(--line);
            transform: translateY(-3px);
        }

        .baris .nilai {
            min-width: 0;
            text-align: right;
            font-weight: 700;
            overflow-wrap: anywhere; /* nama produk panjang tidak merusak layout */
        }

        .baris.hemat .nilai { color: var(--green); }

        .baris.total {
            margin-top: 10px;
            padding-top: 16px;
            border-top: 2px dashed var(--line);
            align-items: center;
        }

        .baris.total .label {
            font-weight: 800;
            color: var(--ink);
        }

        .baris.total .garis { border-bottom-color: transparent; }

        .baris.total .nilai {
            font-size: clamp(1.4rem, 6vw, 1.75rem);
            font-weight: 800;
            color: var(--indigo);
        }

        .badge-hemat {
            display: block;
            width: fit-content;
            margin: 14px auto 0;
            padding: 7px 14px;
            border-radius: 999px;
            background: #dff5ec;
            color: var(--green);
            font-size: .85rem;
            font-weight: 700;
        }

        @keyframes muncul {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: none; }
        }

        /* ---------- Responsif ---------- */
        @media (max-width: 400px) {
            .struk { margin-left: 0; margin-right: 0; padding: 20px 16px 28px; }
            .baris { flex-wrap: wrap; }
            .baris .garis { display: none; }
            .baris .nilai { margin-left: auto; }
            .chip strong { font-size: 1.05rem; }
        }

        @media (prefers-reduced-motion: reduce) {
            * { transition: none !important; animation: none !important; }
        }
    </style>
</head>

<body>
<main class="container">
    <div class="kartu">
        <div class="judul">
            <div class="ikon" aria-hidden="true">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20.6 13.4 13.4 20.6a2 2 0 0 1-2.8 0L3 13V3h10l7.6 7.6a2 2 0 0 1 0 2.8Z"/>
                    <circle cx="7.5" cy="7.5" r="1.2" fill="currentColor"/>
                </svg>
            </div>
            <h1>Hitung Diskon Belanja</h1>
        </div>
        <p class="intro">Masukkan data barang, potongan harganya langsung dihitung.</p>

        <div class="tingkat" aria-label="Tingkat diskon">
            <?php foreach ($tingkatDiskon as $t) { ?>
                <div class="chip<?= ($hasilDitampilkan && abs($persentaseDiskon - $t['persen']) < 0.001) ? ' aktif' : '' ?>">
                    <strong><?= round($t['persen'] * 100) ?>%</strong>
                    <span>mulai <?= e($t['syarat']) ?></span>
                </div>
            <?php } ?>
        </div>

        <form method="POST" novalidate>
            <label for="nama_produk">Nama Produk</label>
            <div class="field">
                <input
                    type="text"
                    id="nama_produk"
                    name="nama_produk"
                    value="<?= e($namaProduk) ?>"
                    maxlength="100"
                    placeholder="Contoh: Mie ayam"
                    autocomplete="off"
                    required
                >
            </div>

            <label for="harga">Harga Satuan</label>
            <div class="field">
                <span class="awalan">Rp</span>
                <input
                    type="number"
                    id="harga"
                    name="harga"
                    value="<?= e($hargaInput) ?>"
                    min="1"
                    max="1000000000"
                    step="any"
                    inputmode="numeric"
                    placeholder="20000"
                    required
                >
            </div>

            <label for="jumlah">Jumlah Barang</label>
            <div class="field">
                <input
                    type="number"
                    id="jumlah"
                    name="jumlah"
                    value="<?= e($jumlahInput) ?>"
                    min="1"
                    max="10000"
                    step="1"
                    inputmode="numeric"
                    required
                >
                <span class="akhiran">buah</span>
            </div>

            <button type="submit">Hitung Diskon</button>
        </form>

        <?php if ($pesanError !== "") { ?>
            <p class="error" role="alert"><?= e($pesanError) ?></p>
        <?php } ?>
    </div>

    <?php if ($hasilDitampilkan) { ?>
        <section class="struk" aria-live="polite">
            <h2>Rincian Belanja</h2>
            <p class="sub">Hasil perhitungan diskon</p>

            <?php foreach ($rincian as $label => $nilai) { ?>
                <?php
                $kelas = "baris";
                if ($label === "Total Bayar") {
                    $kelas .= " total";
                } elseif ($label === "Potongan Harga") {
                    $kelas .= " hemat";
                }
                ?>
                <div class="<?= $kelas ?>">
                    <span class="label"><?= e($label) ?></span>
                    <span class="garis" aria-hidden="true"></span>
                    <span class="nilai"><?= e($nilai) ?></span>
                </div>
            <?php } ?>

            <?php if ($nominalDiskon > 0) { ?>
                <span class="badge-hemat">Kamu hemat <?= e(rupiah($nominalDiskon)) ?></span>
            <?php } ?>
        </section>
    <?php } ?>
</main>
</body>
</html>