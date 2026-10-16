<?php

namespace App\Support;

/**
 * Initial SEWOLAH rental price list (RM per day).
 *
 * agent  = harga ejen biasa
 * vip    = harga ejen VIP (kerap ambil kereta owner)
 * retail = harga sewa kepada pelanggan [min, max]
 *
 * Only retail prices are ever shown to customers. Agent/VIP prices are for
 * the admin panel only. After installation, prices are managed in
 * Admin → Harga Kereta; this list is only used to seed an empty table.
 */
class CarCatalog
{
    /** Category key => [label, default deposit RM] */
    public const CATEGORIES = [
        'ekonomi' => ['Ekonomi', 200],
        'sedan' => ['Sedan & Hatchback', 300],
        'mpv' => ['MPV & Keluarga', 500],
        'suv' => ['SUV', 500],
        'pikap' => ['Pikap 4x4', 500],
        'van' => ['Van', 500],
        'premium' => ['Premium & Mewah', 1000],
        'eksotik' => ['Eksotik & Sport', 3000],
    ];

    /** [brand, model, category, agent, vip, retail_min, retail_max] */
    public const DEFAULTS = [
        ['Perodua', 'Bezza', 'ekonomi', 120, 100, 150, 150],
        ['Perodua', 'Axia (New)', 'ekonomi', 120, 100, 140, 140],
        ['Perodua', 'Myvi Gen 3', 'ekonomi', 130, 100, 150, 150],
        ['Perodua', 'Alza', 'mpv', 160, 140, 200, 200],
        ['Perodua', 'Alza (New)', 'mpv', 200, 170, 280, 280],
        ['Perodua', 'Ativa', 'suv', 170, 150, 220, 220],
        ['Proton', 'Saga (New)', 'ekonomi', 120, 100, 150, 150],
        ['Proton', 'X50', 'suv', 270, 230, 350, 350],
        ['Proton', 'X70', 'suv', 320, 280, 380, 450],
        ['Proton', 'Exora', 'mpv', 160, 140, 220, 220],
        ['Suzuki', 'Swift (Facelift)', 'sedan', 150, 120, 180, 200],
        ['Toyota', 'Vios 2022', 'sedan', 180, 150, 200, 250],
        ['Toyota', 'Camry', 'sedan', 300, 260, 450, 450],
        ['Toyota', 'Hilux 2022', 'pikap', 350, 300, 450, 450],
        ['Toyota', 'Innova', 'mpv', 400, 350, 500, 500],
        ['Toyota', 'Voxy (8 tempat duduk)', 'mpv', 400, 350, 500, 500],
        ['Toyota', 'Harrier 2.0 Full Spec', 'suv', 650, 500, 800, 1000],
        ['Toyota', 'Hiace 2.5 (10 tempat duduk)', 'van', 400, 370, 500, 500],
        ['Toyota', 'Hiace 2.5 (9 tempat duduk)', 'van', 380, 350, 450, 450],
        ['Toyota', 'Alphard SC', 'premium', 800, 700, 1000, 1300],
        ['Toyota', 'Alphard SA', 'premium', 600, 500, 800, 800],
        ['Toyota', 'Vellfire AGH30 Pilot', 'premium', 800, 700, 1000, 1300],
        ['Toyota', 'Vellfire AGH35 (7 tempat duduk)', 'premium', 600, 500, 800, 800],
        ['Toyota', 'Vellfire AGH35 (8 tempat duduk)', 'premium', 600, 500, 800, 800],
        ['Toyota', 'Vellfire ANH20 Kenstyle Kit', 'mpv', 400, 350, 600, 600],
        ['Toyota', 'Vellfire ANH20 Modelista', 'mpv', 400, 350, 600, 600],
        ['Honda', 'City (Facelift 2022)', 'sedan', 170, 150, 200, 250],
        ['Honda', 'Jazz', 'sedan', 150, 120, 180, 200],
        ['Honda', 'Civic FC 1.5 Turbo', 'sedan', 250, 220, 350, 380],
        ['Honda', 'Civic 1.8 NA', 'sedan', 250, 220, 350, 380],
        ['Honda', 'HR-V', 'suv', 220, 180, 380, 380],
        ['Honda', 'BR-V', 'suv', 260, 220, 380, 380],
        ['Nissan', 'Serena Hybrid', 'mpv', 400, 350, 500, 500],
        ['Nissan', 'Navara Stage 2', 'pikap', 300, 250, 450, 450],
        ['Mitsubishi', 'Outlander 2.0 MIVEC (7 tempat duduk)', 'suv', 300, 250, 400, 400],
        ['Mitsubishi', 'Triton (New Facelift)', 'pikap', 350, 300, 450, 450],
        ['Mitsubishi', 'Triton VGT (New Facelift)', 'pikap', 350, 300, 450, 450],
        ['Mitsubishi', 'Triton Athlete', 'pikap', 350, 300, 450, 450],
        ['Ford', 'Ranger Wildtrak', 'pikap', 350, 300, 500, 500],
        ['Ford', 'Ranger XLT', 'pikap', 350, 300, 500, 500],
        ['Ford', 'Mustang 2.3 (Shelby Bodykit)', 'premium', 800, 650, 1000, 1200],
        ['Subaru', 'XV GT Spec', 'suv', 300, 250, 380, 380],
        ['Hyundai', 'Starex 2nd Gen', 'van', 400, 350, 500, 500],
        ['Volkswagen', 'Golf 1.4 (MK6R Conversion)', 'sedan', 300, 250, 450, 450],
        ['Volkswagen', 'Golf GTI 2.0', 'premium', 500, 400, 600, 600],
        ['MINI', 'Cooper S Stage 2', 'premium', 400, 350, 500, 500],
        ['MINI', 'Cooper S', 'premium', 400, 350, 500, 500],
        ['Mercedes-Benz', 'A180', 'premium', 600, 550, 800, 800],
        ['Mercedes-Benz', 'A200', 'premium', 500, 400, 650, 650],
        ['Mercedes-Benz', 'CLA200 (CLA45 Bodykit)', 'premium', 600, 500, 800, 800],
        ['Mercedes-Benz', 'C200 Avantgarde', 'premium', 700, 600, 900, 900],
        ['Mercedes-Benz', 'E200 W212 (E63 Conversion, Stage 2)', 'premium', 550, 450, 700, 700],
        ['Mercedes-Benz', 'E200 W213 Avantgarde', 'premium', 800, 700, 1000, 1000],
        ['Mercedes-Benz', 'GLA45 AMG', 'premium', 700, 600, 900, 1000],
        ['Mercedes-Benz', 'GLC250 AMG', 'premium', 600, 500, 900, 900],
        ['Mercedes-Benz', 'GLC300', 'premium', 600, 700, 1000, 1000],
        ['Mercedes-Benz', 'GLE450 AMG', 'premium', 900, 700, 1200, 1500],
        ['BMW', '320i F30', 'premium', 500, 400, 600, 600],
        ['BMW', '328i G20', 'premium', 600, 500, 800, 800],
        ['BMW', '330e (e-Drive)', 'premium', 500, 450, 600, 600],
        ['BMW', '523i F10 (M5 Kit)', 'premium', 500, 400, 600, 600],
        ['BMW', 'X6 xDrive 3.0', 'premium', 700, 600, 1200, 1200],
        ['BMW', 'i8 Coupe', 'eksotik', 1500, 1000, 2500, 2500],
        ['Audi', 'A4 B8.5 S-Line (S4 Conversion)', 'premium', 500, 400, 600, 600],
        ['Audi', 'A5', 'premium', 600, 500, 700, 700],
        ['Audi', 'A5 Stage 2', 'premium', 600, 500, 700, 700],
        ['Porsche', 'Cayman 718 (GT4 Bodykit)', 'eksotik', 1500, 1000, 2000, 2000],
    ];

    public static function categoryLabel(?string $key): string
    {
        return self::CATEGORIES[$key][0] ?? ucfirst((string) $key);
    }

    public static function defaultDeposit(?string $key): int
    {
        return self::CATEGORIES[$key][1] ?? 500;
    }
}
