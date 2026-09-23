<?php
/**
 * Demo/starter content: category images, curated product names & copy (EN/TR/DE),
 * corporate page bodies, homepage settings and UI strings.
 * Safe to re-run — it upserts. Products beyond the curated count stay drafts.
 *
 *   php scripts/seed_content.php
 */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { exit("CLI only.\n"); }
define('BASE_DIR', dirname(__DIR__));
require BASE_DIR . '/app/helpers.php';
spl_autoload_register(function ($c) {
    if (strncmp($c, 'App\\', 4) === 0) {
        $p = BASE_DIR . '/app/' . str_replace('\\', '/', substr($c, 4)) . '.php';
        if (is_file($p)) require $p;
    }
});
use App\Core\Database as DB;
$cfg = require BASE_DIR . '/config/config.php';
DB::init($cfg['db']);

$L = [];
foreach (DB::all('SELECT id, code FROM languages') as $r) { $L[$r['code']] = (int) $r['id']; }
$L += ['en' => 1, 'tr' => 2, 'de' => 3];

/* -------------------------------------------------------------------------
 * 1. Category cover images
 * ---------------------------------------------------------------------- */
$catRows = DB::all('SELECT id, slug FROM categories');
$catId = [];
foreach ($catRows as $r) { $catId[$r['slug']] = (int) $r['id']; }

function firstImageFor(int $catId): ?string {
    return DB::value(
        'SELECT pi.path FROM product_images pi JOIN products p ON p.id = pi.product_id
         WHERE p.category_id = ? ORDER BY pi.id LIMIT 1', [$catId]
    );
}
$fallback = DB::value("SELECT path FROM product_images WHERE path LIKE '%mood%' ORDER BY id LIMIT 1")
    ?: DB::value('SELECT path FROM product_images ORDER BY id LIMIT 1');

foreach ($catId as $slug => $id) {
    $img = firstImageFor($id) ?: $fallback;
    if ($img) {
        DB::query('UPDATE categories SET image = ? WHERE id = ?', [$img, $id]);
    }
}
echo "Category images set.\n";

/* -------------------------------------------------------------------------
 * 2. Curated product content
 * ---------------------------------------------------------------------- */
$pool = [
  'bathroom' => [
    ['en'=>'Combed Cotton Bath Towel','tr'=>'Taranmış Pamuk Banyo Havlusu','de'=>'Badetuch aus gekämmter Baumwolle'],
    ['en'=>'Zero-Twist Towel Collection','tr'=>'Bukatsız (Zero-Twist) Havlu Koleksiyonu','de'=>'Zero-Twist Handtuch-Kollektion'],
    ['en'=>'Waffle Weave Bath Set','tr'=>'Gofre Dokuma Banyo Seti','de'=>'Waffelpiqué Bad-Set'],
    ['en'=>'Ribbed Spa Towel','tr'=>'Fitilli Spa Havlusu','de'=>'Geripptes Spa-Handtuch'],
    ['en'=>'Jacquard Border Towel','tr'=>'Jakarlı Bordürlü Havlu','de'=>'Handtuch mit Jacquardbordüre'],
  ],
  'beach-pool' => [
    ['en'=>'Sand-Free Beach Towel','tr'=>'Kum Tutmayan Plaj Havlusu','de'=>'Sandfreies Strandtuch'],
    ['en'=>'Ombré Pareo','tr'=>'Ombre Pareo','de'=>'Ombré-Pareo'],
    ['en'=>'Jacquard Beach Towel','tr'=>'Jakarlı Plaj Havlusu','de'=>'Jacquard-Strandtuch'],
    ['en'=>'Striped Pool Towel','tr'=>'Çizgili Havuz Havlusu','de'=>'Gestreiftes Pooltuch'],
  ],
  'bedding' => [
    ['en'=>'Percale Duvet Set','tr'=>'Percale Nevresim Takımı','de'=>'Perkal-Bettwäsche-Set'],
    ['en'=>'Sateen Bed Linen','tr'=>'Saten Nevresim','de'=>'Satin-Bettwäsche'],
    ['en'=>'Washed Cotton Duvet Cover','tr'=>'Yıkanmış Pamuk Nevresim','de'=>'Bettbezug aus gewaschener Baumwolle'],
    ['en'=>'Waffle Bedspread','tr'=>'Gofre Yatak Örtüsü','de'=>'Waffelpiqué-Tagesdecke'],
  ],
  'hotels-sauna' => [
    ['en'=>'Hotel Bath Towel 500 g/m²','tr'=>'Otel Banyo Havlusu 500 g/m²','de'=>'Hotel-Badetuch 500 g/m²'],
    ['en'=>'Sauna Kilt','tr'=>'Sauna Peştemali','de'=>'Sauna-Kilt'],
    ['en'=>'Waffle Hotel Robe','tr'=>'Gofre Otel Bornozu','de'=>'Waffelpiqué-Hotelbademantel'],
    ['en'=>'Combed Cotton Hotel Set','tr'=>'Taranmış Pamuk Otel Seti','de'=>'Hotel-Set aus gekämmter Baumwolle'],
    ['en'=>'Contract Pool & Spa Towel','tr'=>'Kontrat Havuz & Spa Havlusu','de'=>'Objekt Pool- & Spa-Tuch'],
  ],
  'kitchen' => [
    ['en'=>'Honeycomb Kitchen Towel','tr'=>'Bal Peteği Mutfak Havlusu','de'=>'Waben-Küchentuch'],
    ['en'=>'Terry Kitchen Towel','tr'=>'Havlu Dokuma Mutfak Bezi','de'=>'Frottier-Küchentuch'],
    ['en'=>'Striped Cotton Tea Towel','tr'=>'Çizgili Pamuk Kurulama Bezi','de'=>'Gestreiftes Baumwoll-Geschirrtuch'],
    ['en'=>'Waffle Dish Cloth Set','tr'=>'Gofre Mutfak Bezi Seti','de'=>'Waffel-Spültuch-Set'],
  ],
  'promotion' => [
    ['en'=>'Custom Jacquard Border Towel','tr'=>'Özel Jakarlı Bordürlü Havlu','de'=>'Handtuch mit individueller Jacquardbordüre'],
    ['en'=>'Printed Border Promotional Towel','tr'=>'Baskılı Bordürlü Promosyon Havlusu','de'=>'Werbehandtuch mit bedruckter Bordüre'],
    ['en'=>'Embroidered Logo Towel','tr'=>'Nakış Logolu Havlu','de'=>'Handtuch mit Logo-Stickerei'],
    ['en'=>'Terry Border Towel','tr'=>'Havlu Bordürlü Havlu','de'=>'Handtuch mit Frottierbordüre'],
  ],
];

$shortDesc = [
  'en' => '%s — woven in Denizli from long-staple cotton for lasting softness and absorbency. Available for retail and contract, with custom sizes, colours and packaging.',
  'tr' => '%s — uzun elyaflı pamuktan Denizli’de dokunur; kalıcı yumuşaklık ve emicilik sunar. Perakende ve kontrat için özel ebat, renk ve ambalaj seçenekleriyle üretilir.',
  'de' => '%s — in Denizli aus langstapeliger Baumwolle gewebt, für dauerhafte Weichheit und Saugkraft. Für Handel und Objekt, mit individuellen Größen, Farben und Verpackungen.',
];
$longDesc = [
  'en' => '<p>%s is part of Zoen Tekstil’s core range, produced on our own looms with yarn selected and dyed in-house. Every piece is finished for low lint, colour fastness and a full, dense handle.</p><p>We develop private-label programmes end to end: construction, weight, border design, embroidery, and retail-ready packaging. Minimums and lead times are shared on request.</p>',
  'tr' => '<p>%s, Zoen Tekstil’in ana koleksiyonunun bir parçasıdır; kendi tezgâhlarımızda, bünyemizde seçilip boyanan ipliklerle üretilir. Her ürün düşük tüylenme, renk haslığı ve dolgun bir tuşe için terbiye edilir.</p><p>Özel marka (private label) programlarını uçtan uca geliştiriyoruz: konstrüksiyon, gramaj, bordür tasarımı, nakış ve rafa hazır ambalaj. Adet ve termin bilgileri talep üzerine paylaşılır.</p>',
  'de' => '<p>%s gehört zum Kernsortiment von Zoen Tekstil und wird auf eigenen Webstühlen mit im Haus ausgewähltem und gefärbtem Garn gefertigt. Jedes Stück wird auf geringe Fusselbildung, Farbechtheit und einen vollen Griff ausgerüstet.</p><p>Private-Label-Programme entwickeln wir von A bis Z: Konstruktion, Gewicht, Bordürendesign, Stickerei und verkaufsfertige Verpackung. Mindestmengen und Lieferzeiten auf Anfrage.</p>',
];
$attrs = [
  'en' => ["Composition | 100% cotton","Weight | 450–600 g/m²","Origin | Denizli, Türkiye","Customisation | Size, colour, border, embroidery","Certificates | OEKO-TEX® STANDARD 100"],
  'tr' => ["Kompozisyon | %100 pamuk","Gramaj | 450–600 g/m²","Menşe | Denizli, Türkiye","Özelleştirme | Ebat, renk, bordür, nakış","Sertifikalar | OEKO-TEX® STANDARD 100"],
  'de' => ["Zusammensetzung | 100% Baumwolle","Gewicht | 450–600 g/m²","Herkunft | Denizli, Türkei","Anpassung | Größe, Farbe, Bordüre, Stickerei","Zertifikate | OEKO-TEX® STANDARD 100"],
];

function uniqueSlug(string $base, int $langId, int $exceptId): string {
    $s = $base; $n = 2;
    while (DB::value('SELECT 1 FROM product_translations WHERE slug = ? AND language_id = ? AND product_id <> ?', [$s, $langId, $exceptId])) {
        $s = $base . '-' . $n++;
    }
    return $s;
}

// Bootstrap the Bedding category if it has no products (its source images were TIFF).
if (!empty($catId['bedding'])
    && (int) DB::value('SELECT COUNT(*) FROM products WHERE category_id = ?', [$catId['bedding']]) === 0) {
    $beddingImgs = [
        'uploads/2026/08/capital-grey-mood-08935ba3.jpg',
        'uploads/2026/08/beck-mood-2-c5884f4a.jpg',
        'uploads/2026/08/capital-pataki-gkrez-1-6aa5764b.jpg',
        'uploads/2026/08/beck-mood-1-1-df0c93f7.jpg',
    ];
    foreach ($beddingImgs as $img) {
        if (!is_file(BASE_DIR . '/' . $img)) { continue; }
        $newId = DB::insert('products', [
            'category_id' => $catId['bedding'], 'sku' => '', 'cover_image' => $img,
            'is_active' => 0, 'is_featured' => 0, 'sort' => 0,
            'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
        ]);
        DB::insert('product_images', ['product_id' => $newId, 'path' => $img, 'alt' => '', 'sort' => 0]);
    }
    echo "Bedding category bootstrapped.\n";
}

$featuredPicked = 0;
foreach ($pool as $slug => $names) {
    if (empty($catId[$slug])) continue;
    $products = DB::all('SELECT id FROM products WHERE category_id = ? ORDER BY id', [$catId[$slug]]);
    foreach ($names as $i => $set) {
        if (!isset($products[$i])) break;
        $pid = (int) $products[$i]['id'];

        DB::query('UPDATE products SET is_active = 1, sku = ? WHERE id = ?',
            [strtoupper(substr($slug, 0, 3)) . '-' . str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT), $pid]);

        // spread ~8 featured across categories
        $feat = ($i === 0 && $featuredPicked < 8) ? 1 : 0;
        if ($feat) { $featuredPicked++; }
        DB::query('UPDATE products SET is_featured = ? WHERE id = ?', [$feat, $pid]);

        foreach ($L as $code => $lid) {
            $name = $set[$code];
            $slugBase = slugify($name);
            $slugFinal = uniqueSlug($slugBase, $lid, $pid);
            $payload = [
                'name'             => $name,
                'slug'             => $slugFinal,
                'short_desc'       => sprintf($shortDesc[$code], $name),
                'description'      => sprintf($longDesc[$code], $name),
                'meta_title'       => $name . ' | Zoen Tekstil',
                'meta_description' => sprintf($shortDesc[$code], $name),
            ];
            $ex = DB::one('SELECT id FROM product_translations WHERE product_id = ? AND language_id = ?', [$pid, $lid]);
            if ($ex) {
                DB::update('product_translations', $payload, 'id = :id', ['id' => $ex['id']]);
            } else {
                DB::insert('product_translations', $payload + ['product_id' => $pid, 'language_id' => $lid]);
            }
            DB::delete('product_attributes', 'product_id = :p AND language_id = :l', ['p' => $pid, 'l' => $lid]);
            foreach ($attrs[$code] as $line) {
                [$lab, $val] = array_map('trim', explode('|', $line, 2));
                DB::insert('product_attributes', ['product_id' => $pid, 'language_id' => $lid, 'label' => $lab, 'value' => $val]);
            }
        }
    }
}
echo "Curated products activated ($featuredPicked featured).\n";

/* -------------------------------------------------------------------------
 * 3. Corporate page bodies
 * ---------------------------------------------------------------------- */
$pages = [
  'about' => [
    'en' => ['About Zoen Tekstil', 'Zoen Tekstil is an integrated home-textile manufacturer based in Denizli — the heart of Türkiye’s towelling industry.',
      '<p>Founded on three generations of weaving know-how, Zoen Tekstil produces towels, bathrobes, beach textiles, kitchen textiles and bedding for retailers, hotel groups and spa operators worldwide.</p><p>Weaving, yarn and piece dyeing, printing, embroidery and confection all take place under one roof, which lets us control quality from raw cotton to the folded, labelled product on the shelf.</p><p>We work as a private-label partner: our design team develops constructions, weights, borders and packaging to each customer’s brief, backed by OEKO-TEX®, GOTS and GRS certified processes.</p>'],
    'tr' => ['Zoen Tekstil Hakkında', 'Zoen Tekstil, Türkiye havlu sektörünün merkezi Denizli’de faaliyet gösteren entegre bir ev tekstili üreticisidir.',
      '<p>Üç kuşaklık dokuma birikimi üzerine kurulan Zoen Tekstil; dünya genelinde perakendeciler, otel grupları ve spa işletmeleri için havlu, bornoz, plaj tekstilleri, mutfak tekstilleri ve nevresim üretir.</p><p>Dokuma, iplik ve parça boya, baskı, nakış ve konfeksiyon tek çatı altında yapılır; böylece kaliteyi ham pamuktan rafa çıkan katlanmış, etiketli ürüne kadar kontrol ederiz.</p><p>Özel marka (private label) iş ortağı olarak çalışıyoruz: tasarım ekibimiz her müşterinin briefine göre konstrüksiyon, gramaj, bordür ve ambalaj geliştirir; süreçlerimiz OEKO-TEX®, GOTS ve GRS sertifikalıdır.</p>'],
    'de' => ['Über Zoen Tekstil', 'Zoen Tekstil ist ein integrierter Heimtextilhersteller in Denizli – dem Zentrum der türkischen Frottierindustrie.',
      '<p>Auf drei Generationen Webkompetenz aufgebaut, fertigt Zoen Tekstil Handtücher, Bademäntel, Strand- und Küchentextilien sowie Bettwäsche für Handel, Hotelgruppen und Spa-Betreiber weltweit.</p><p>Weberei, Garn- und Stückfärberei, Druck, Stickerei und Konfektion finden unter einem Dach statt – so steuern wir die Qualität von der Rohbaumwolle bis zum gefalteten, etikettierten Produkt im Regal.</p><p>Wir arbeiten als Private-Label-Partner: Unser Designteam entwickelt Konstruktionen, Gewichte, Bordüren und Verpackungen nach Kundenbriefing – abgesichert durch OEKO-TEX®-, GOTS- und GRS-zertifizierte Prozesse.</p>'],
  ],
  'production' => [
    'en' => ['Production', 'An integrated mill: from warping and weaving to dyeing, printing and confection.',
      '<p>Our facility combines rapier and air-jet towel looms with dobby and jacquard capability, a yarn-dye plant, reactive and pigment printing, computerised embroidery and a full cut-and-sew department.</p><p>In-house laboratories test absorbency, pilling, dimensional stability, colour fastness and pH on every batch. A biological water-treatment and heat-recovery plant supports responsible production.</p><ul><li>Warp preparation &amp; sizing</li><li>Terry &amp; flat weaving</li><li>Yarn and piece dyeing</li><li>Digital &amp; rotary printing</li><li>Embroidery, cut &amp; sew, packaging</li></ul>'],
    'tr' => ['Üretim', 'Entegre tesis: çözgü ve dokumadan boya, baskı ve konfeksiyona.',
      '<p>Tesisimizde rapier ve hava jetli havlu tezgâhları, armür ve jakar kapasitesi, iplik boya, reaktif ve pigment baskı, bilgisayarlı nakış ve tam donanımlı kesim-dikim bölümü bir arada bulunur.</p><p>Bünyemizdeki laboratuvarlar her partide emicilik, boncuklanma, boyutsal kararlılık, renk haslığı ve pH testleri yapar. Biyolojik atık su arıtma ve ısı geri kazanım tesisi sorumlu üretimi destekler.</p><ul><li>Çözgü hazırlık ve haşıl</li><li>Havlu ve düz dokuma</li><li>İplik ve parça boya</li><li>Dijital ve rotasyon baskı</li><li>Nakış, kesim-dikim, ambalaj</li></ul>'],
    'de' => ['Produktion', 'Integrierte Weberei: von Schären und Weben bis Färben, Drucken und Konfektion.',
      '<p>Unser Werk vereint Greifer- und Luftdüsen-Frottierwebstühle mit Schaft- und Jacquardtechnik, eine Garnfärberei, Reaktiv- und Pigmentdruck, computergesteuerte Stickerei und eine komplette Konfektionsabteilung.</p><p>Hauseigene Labore prüfen Saugfähigkeit, Pilling, Dimensionsstabilität, Farbechtheit und pH-Wert jeder Charge. Eine biologische Abwasserreinigung mit Wärmerückgewinnung unterstützt verantwortungsvolle Produktion.</p><ul><li>Schären &amp; Schlichten</li><li>Frottier- &amp; Flachweberei</li><li>Garn- und Stückfärberei</li><li>Digital- &amp; Rotationsdruck</li><li>Stickerei, Konfektion, Verpackung</li></ul>'],
  ],
  'quality' => [
    'en' => ['Quality & Certificates', 'Certified processes and batch-level testing on every order.',
      '<p>Zoen Tekstil operates to ISO 9001 quality management. Our products and processes are covered by OEKO-TEX® STANDARD 100, GOTS (organic cotton) and GRS (recycled content) certification.</p><p>Each production lot is checked for weight, absorbency time, dimensional change after washing, rubbing and washing colour fastness, and appearance after five home launderings before it is released.</p>'],
    'tr' => ['Kalite & Sertifikalar', 'Her siparişte sertifikalı süreçler ve parti bazında test.',
      '<p>Zoen Tekstil, ISO 9001 kalite yönetimi ile çalışır. Ürün ve süreçlerimiz OEKO-TEX® STANDARD 100, GOTS (organik pamuk) ve GRS (geri dönüştürülmüş içerik) sertifikaları kapsamındadır.</p><p>Her üretim partisi; gramaj, ıslanma (emicilik) süresi, yıkama sonrası boyut değişimi, sürtme ve yıkama renk haslığı ve beş ev yıkaması sonrası görünüm açısından kontrol edilerek sevk edilir.</p>'],
    'de' => ['Qualität & Zertifikate', 'Zertifizierte Prozesse und Chargenprüfung bei jedem Auftrag.',
      '<p>Zoen Tekstil arbeitet nach ISO 9001. Unsere Produkte und Prozesse sind durch OEKO-TEX® STANDARD 100, GOTS (Bio-Baumwolle) und GRS (Recyclinganteil) zertifiziert.</p><p>Jedes Los wird vor der Freigabe auf Gewicht, Saugzeit, Maßänderung nach Wäsche, Reib- und Waschechtheit sowie Aussehen nach fünf Haushaltswäschen geprüft.</p>'],
  ],
  'sustainability' => [
    'en' => ['Sustainability', 'Lower water, cleaner chemistry, longer-lasting textiles.',
      '<p>We recover heat and treat all process water biologically before discharge. Our chemical inventory follows the ZDHC MRSL, and we prioritise BCI, organic and recycled fibres.</p><p>Durable construction is its own form of sustainability: a towel that keeps its softness and absorbency through hundreds of washes is replaced less often.</p>'],
    'tr' => ['Sürdürülebilirlik', 'Daha az su, daha temiz kimya, daha uzun ömürlü tekstil.',
      '<p>Isıyı geri kazanır ve tüm proses suyunu deşarj öncesinde biyolojik olarak arıtırız. Kimyasal envanterimiz ZDHC MRSL’ye uygundur; BCI, organik ve geri dönüştürülmüş elyafı önceliklendiririz.</p><p>Dayanıklı konstrüksiyon başlı başına bir sürdürülebilirliktir: yüzlerce yıkama boyunca yumuşaklığını ve emiciliğini koruyan bir havlu daha seyrek değiştirilir.</p>'],
    'de' => ['Nachhaltigkeit', 'Weniger Wasser, sauberere Chemie, langlebigere Textilien.',
      '<p>Wir gewinnen Wärme zurück und reinigen das gesamte Prozesswasser biologisch vor der Einleitung. Unser Chemikalieninventar folgt der ZDHC MRSL; wir bevorzugen BCI-, Bio- und Recyclingfasern.</p><p>Langlebige Konstruktion ist selbst Nachhaltigkeit: Ein Handtuch, das über hunderte Wäschen weich und saugfähig bleibt, wird seltener ersetzt.</p>'],
  ],
];
foreach ($pages as $slug => $byLang) {
    $pid = DB::value('SELECT id FROM pages WHERE slug = ?', [$slug]);
    if (!$pid) continue;
    foreach ($byLang as $code => $d) {
        $lid = $L[$code];
        $payload = [
            'title'            => $d[0],
            'body'             => $d[2],
            'meta_title'       => strip_tags($d[0]) . ' | Zoen Tekstil',
            'meta_description' => $d[1],
        ];
        $ex = DB::one('SELECT id FROM page_translations WHERE page_id = ? AND language_id = ?', [$pid, $lid]);
        if ($ex) { DB::update('page_translations', $payload, 'id = :id', ['id' => $ex['id']]); }
        else { DB::insert('page_translations', $payload + ['page_id' => $pid, 'language_id' => $lid, 'og_image' => '']); }
    }
}
echo "Corporate pages written.\n";

/* -------------------------------------------------------------------------
 * 4. Settings
 * ---------------------------------------------------------------------- */
$set = [
  'site_name' => 'Zoen Tekstil',
  'contact_email' => 'info@zoentekstil.com',
  'contact_phone' => '+90 541 281 41 95',
  'contact_phone2' => '+90 533 657 62 74',
  'contact_address' => "Merkez Ofis\nIşıktepe Mah. Fırtınalı Sok. No:2 İç Kapı No:39\nNilüfer / BURSA\n\nFabrika\nZafer Mah. 1007 Sok. No:41, Gümüşler\nMerkezefendi / DENİZLİ",
  'office1_label' => 'Merkez Ofis',
  'office1_address' => "Işıktepe Mah. Fırtınalı Sok. No:2 İç Kapı No:39\nNilüfer / BURSA",
  'office2_label' => 'Fabrika',
  'office2_address' => "Zafer Mah. 1007 Sok. No:41, Gümüşler\nMerkezefendi / DENİZLİ",
  'working_hours' => 'Hafta içi 08:30 – 18:00',
  'social_linkedin' => 'https://www.linkedin.com/',
  'social_instagram' => 'https://www.instagram.com/',
  'social_facebook' => 'https://www.facebook.com/',
  'years_established' => '25',
  'stat_capacity' => '600',
  'stat_countries' => '30',
  'marquee_items' => 'Banyo Havlusu, Plaj & Havuz, Otel & Sauna, Promosyon Havlusu, Yatak Grubu, Peştemal, Bornoz, %100 Pamuk, OEKO-TEX®, GOTS, Private Label, Denizli / Bursa',
];
foreach ($set as $k => $v) {
    DB::query('INSERT INTO settings (`key`,`value`) VALUES (?,?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)', [$k, $v]);
}
$setT = [
  'footer_eyebrow' => ['en'=>'Home Textile Manufacturer','tr'=>'Ev Tekstili Üreticisi','de'=>'Heimtextil-Hersteller'],
  'site_tagline'   => [
    'en'=>'Turkish towel & bathrobe manufacturer since 1999.',
    'tr'=>'1999’dan bu yana havlu ve bornoz üreticisi.',
    'de'=>'Türkischer Handtuch- & Bademantelhersteller seit 1999.'],
  'topbar_note'    => [
    'en'=>'Denizli · Integrated home-textile manufacturer',
    'tr'=>'Denizli · Entegre ev tekstili üreticisi',
    'de'=>'Denizli · Integrierter Heimtextilhersteller'],
  'footer_about'   => [
    'en'=>'Zoen Tekstil is an integrated home-textile manufacturer in Denizli, Türkiye — towels, bathrobes, bedding, beach and kitchen textiles for retail and hotel/contract customers, private-label and OEKO-TEX® certified.',
    'tr'=>'Zoen Tekstil, Denizli’de entegre bir ev tekstili üreticisidir — perakende ve otel/kontrat müşterileri için havlu, bornoz, nevresim, plaj ve mutfak tekstilleri; özel marka ve OEKO-TEX® sertifikalı.',
    'de'=>'Zoen Tekstil ist ein integrierter Heimtextilhersteller in Denizli, Türkei — Handtücher, Bademäntel, Bettwäsche, Strand- und Küchentextilien für Handel und Hotel/Objekt, Private Label und OEKO-TEX®-zertifiziert.'],
  'home_meta_title' => [
    'en'=>'Zoen Tekstil — Turkish Towel, Bathrobe & Home Textile Manufacturer (Denizli)',
    'tr'=>'Zoen Tekstil — Havlu, Bornoz ve Ev Tekstili Üreticisi | Denizli',
    'de'=>'Zoen Tekstil — Handtuch-, Bademantel- & Heimtextilhersteller aus Denizli'],
  'home_meta_description' => [
    'en'=>'Zoen Tekstil is an integrated Denizli mill manufacturing towels, bathrobes, bedding, beach and kitchen textiles for retailers and hotels worldwide. Private-label, OEKO-TEX® / GOTS certified, custom sizes and packaging.',
    'tr'=>'Zoen Tekstil; Denizli’de havlu, bornoz, nevresim, plaj ve mutfak tekstilleri üreten entegre bir tesistir. Perakende ve otel projeleri için özel marka üretim, OEKO-TEX® / GOTS sertifikalı, özel ebat ve ambalaj.',
    'de'=>'Zoen Tekstil fertigt in Denizli Handtücher, Bademäntel, Bettwäsche, Strand- und Küchentextilien für Handel und Hotellerie weltweit. Private Label, OEKO-TEX®- / GOTS-zertifiziert, individuelle Größen und Verpackungen.'],
  'products_meta_description' => [
    'en'=>'Explore Zoen Tekstil home textile collections — bathroom towels & bathrobes, beach & pool, bedding, hotel & spa, kitchen and promotional textiles. Wholesale and contract, made in Denizli.',
    'tr'=>'Zoen Tekstil ev tekstili koleksiyonları — banyo havluları ve bornoz, plaj & havuz, nevresim, otel & spa, mutfak ve promosyon tekstilleri. Toptan ve kontrat, Denizli üretimi.',
    'de'=>'Heimtextil-Kollektionen von Zoen Tekstil — Badhandtücher & Bademäntel, Strand & Pool, Bettwäsche, Hotel & Spa, Küche und Werbetextilien. Großhandel und Objekt, hergestellt in Denizli.'],
  'contact_meta_description' => [
    'en'=>'Contact Zoen Tekstil for wholesale, private-label and hotel/contract home textile enquiries — towels, bathrobes and bedding from Denizli, Türkiye.',
    'tr'=>'Toptan, özel marka ve otel/kontrat ev tekstili talepleriniz için Zoen Tekstil ile iletişime geçin — Denizli’den havlu, bornoz ve nevresim.',
    'de'=>'Kontaktieren Sie Zoen Tekstil für Großhandel, Private Label und Hotel/Objekt — Handtücher, Bademäntel und Bettwäsche aus Denizli, Türkei.'],
];
DB::query('INSERT INTO settings (`key`,`value`) VALUES ("meta_keywords",?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)',
  ['towel manufacturer, bathrobe manufacturer, Turkish towels, Denizli textile, home textile supplier, hotel towels, private label towels, bedding manufacturer, OEKO-TEX towels, wholesale towels']);
foreach ($setT as $k => $byLang) {
    foreach ($byLang as $code => $v) {
        $lid = $L[$code];
        DB::query('INSERT INTO setting_translations (`key`,language_id,`value`) VALUES (?,?,?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)', [$k, $lid, $v]);
    }
}
echo "Settings updated.\n";

/* -------------------------------------------------------------------------
 * 5. UI strings for the new theme (tr + de; en uses code defaults)
 * ---------------------------------------------------------------------- */
$ui = [
  'nav.corporate'   => ['tr'=>'Kurumsal','de'=>'Unternehmen'],
  'nav.production'  => ['tr'=>'Üretim','de'=>'Produktion'],
  'nav.about'       => ['tr'=>'Hakkımızda','de'=>'Über uns'],
  'products.all'    => ['tr'=>'Tüm Ürünler','de'=>'Alle Produkte'],
  'products.items'  => ['tr'=>'ürün','de'=>'Produkte'],
  'common.discover' => ['tr'=>'Keşfet','de'=>'Entdecken'],
  'common.view'     => ['tr'=>'İncele','de'=>'Ansehen'],
  'common.read_more'=> ['tr'=>'Devamını oku','de'=>'Mehr lesen'],
  'common.skip'     => ['tr'=>'İçeriğe geç','de'=>'Zum Inhalt'],
  'contact.title'   => ['tr'=>'Bize ulaşın','de'=>'Kontakt aufnehmen'],
  'contact.intro' => ['tr'=>'Ürün gruplarımız, özel üretim ve numune talepleriniz için ekibimizle iletişime geçin.','de'=>'Für Produktgruppen, Sonderanfertigungen und Musteranfragen kontaktieren Sie unser Team.'],
  'contact.info_title' => ['tr'=>'İletişim bilgileri','de'=>'Kontaktdaten'],
  'contact.form_title' => ['tr'=>'Bize mesaj gönderin','de'=>'Schreiben Sie uns'],
  'contact.form_intro' => ['tr'=>'Formu doldurun, en kısa sürede size dönüş yapalım.','de'=>'Füllen Sie das Formular aus, wir melden uns schnellstmöglich.'],
  'contact.hours'      => ['tr'=>'Çalışma saatleri','de'=>'Öffnungszeiten'],
  'home.years'      => ['tr'=>'yıllık üretim','de'=>'Jahre Fertigung'],
  'home.stat_capacity' => ['tr'=>'ton / ay','de'=>'Tonnen / Monat'],
  'home.stat_countries'=> ['tr'=>'ihracat pazarı','de'=>'Exportmärkte'],
  'home.stat_lines' => ['tr'=>'ürün grubu','de'=>'Produktgruppen'],
  'home.stat_sites' => ['tr'=>'tesis — Bursa & Denizli','de'=>'Standorte — Bursa & Denizli'],
  'home.stat_certs' => ['tr'=>'& GOTS talep üzerine','de'=>'& GOTS auf Anfrage'],
  'home.badge_n'    => ['tr'=>'%100','de'=>'100%'],
  'home.badge_l'    => ['tr'=>'pamuk','de'=>'Baumwolle'],
  'home.collections_title' => ['tr'=>'Her mekân için tekstil','de'=>'Textilien für jeden Raum'],
  'home.featured_title'    => ['tr'=>'Koleksiyonumuzdan seçmeler','de'=>'Ausgewählt aus unserem Sortiment'],
  'home.cta_title'  => ['tr'=>'Yeni koleksiyonunuzu birlikte kuralım','de'=>'Bauen wir Ihre nächste Kollektion'],
  'home.why_eyebrow'=> ['tr'=>'Neden Zoen Tekstil','de'=>'Warum Zoen Tekstil'],
  'home.why_title'  => ['tr'=>'Tedarikçi değil, çözüm ortağı','de'=>'Ein Partner, kein bloßer Lieferant'],
  'home.value_1_t'  => ['tr'=>'Entegre tesis','de'=>'Integrierte Weberei'],
  'home.value_1_d'  => ['tr'=>'Dokuma, boya, baskı ve konfeksiyon tek çatı altında; tam kalite kontrolü.','de'=>'Weberei, Färberei, Druck und Konfektion unter einem Dach – volle Qualitätskontrolle.'],
  'home.value_2_t'  => ['tr'=>'Sertifikalı ve sorumlu','de'=>'Zertifiziert & verantwortungsvoll'],
  'home.value_2_d'  => ['tr'=>'OEKO-TEX, GOTS ve GRS sertifikalı üretim, su geri kazanım sistemleri.','de'=>'OEKO-TEX-, GOTS- und GRS-zertifiziert mit Wasserrückgewinnung.'],
  'home.value_3_t'  => ['tr'=>'Özel markaya hazır','de'=>'Private-Label-fähig'],
  'home.value_3_d'  => ['tr'=>'Tasarım ve iplik seçiminden ambalaja kadar markanıza göre.','de'=>'Von Design und Garnwahl bis Verpackung – auf Ihre Marke.'],
  'home.value_4_t'  => ['tr'=>'Perakende ve kontrat','de'=>'Handel & Objekt'],
  'home.value_4_d'  => ['tr'=>'Perakendeciler, otel & resort grupları, spa ve wellness tesisleri için üretim.','de'=>'Fertigung für Händler, Hotel- & Resortgruppen, Spa- und Wellnessanlagen.'],
  'footer.eyebrow'  => ['tr'=>'Ev Tekstili Üreticisi','de'=>'Heimtextil-Hersteller'],
  'footer.headline_1' => ['tr'=>'Konfor,','de'=>'Komfort,'],
  'footer.headline_2' => ['tr'=>'her ipliğe dokunur.','de'=>'in jeden Faden gewebt.'],
  'footer.tagline_caps' => ['tr'=>'Havlu · Bornoz · Nevresim — Denizli','de'=>'Handtücher · Bademäntel · Bettwäsche — Denizli'],
  'footer.address'  => ['tr'=>'Adres','de'=>'Adresse'],
  'footer.company'  => ['tr'=>'Şirket','de'=>'Unternehmen'],
  'footer.follow'   => ['tr'=>'Takip edin','de'=>'Folgen'],
  'footer.reach'    => ['tr'=>'Bize ulaşın','de'=>'Kontakt aufnehmen'],
  'footer.made_by'  => ['tr'=>'Tasarım','de'=>'Design'],
  'footer.rights'   => ['tr'=>'Tüm hakları saklıdır.','de'=>'Alle Rechte vorbehalten.'],
  'product.related' => ['tr'=>'Bunları da beğenebilirsiniz','de'=>'Das könnte Ihnen auch gefallen'],
  'product.related_eyebrow' => ['tr'=>'Zoen’den daha fazlası','de'=>'Mehr von Zoen'],
  'common.back_to_top' => ['tr'=>'Yukarı çık','de'=>'Nach oben'],
  'common.close' => ['tr'=>'Kapat','de'=>'Schließen'],
  'form.thanks_title' => ['tr'=>'Teşekkürler','de'=>'Vielen Dank'],
  'blog.title'      => ['tr'=>'Blog','de'=>'Journal'],
  'blog.eyebrow'    => ['tr'=>'Bilgi Merkezi','de'=>'Wissen'],
  'blog.intro'      => ['tr'=>'Havlu, bornoz ve ev tekstili üzerine rehberler ve sektör içgörüleri.','de'=>'Leitfäden und Einblicke rund um Handtücher, Bademäntel und Heimtextilien.'],
  'blog.empty'      => ['tr'=>'Henüz yazı yok.','de'=>'Noch keine Artikel.'],
  'blog.more'       => ['tr'=>'Diğer yazılar','de'=>'Weitere Artikel'],
  'blog.cta'        => ['tr'=>'Üretim ortağı mı arıyorsunuz?','de'=>'Suchen Sie einen Produktionspartner?'],
  'faq.eyebrow'     => ['tr'=>'Sık Sorulan Sorular','de'=>'Häufige Fragen'],
  'faq.title'       => ['tr'=>'Merak edilenler','de'=>'Häufig gestellte Fragen'],
  'faq.moq_q' => ['tr'=>'Minimum sipariş adediniz nedir?','de'=>'Wie hoch ist die Mindestbestellmenge?'],
  'faq.moq_a' => ['tr'=>'Minimum adet; ürün grubuna, gramaja ve renk sayısına göre değişir. İplik boyalı ürünlerde adet, düz beyaz ürünlere göre daha yüksektir. İhtiyacınızı iletin, size özel bir adet ve fiyat paylaşalım.','de'=>'Die Mindestmenge hängt von Produktgruppe, Grammatur und Farbanzahl ab. Garngefärbte Artikel haben eine höhere Menge als reines Weiß. Teilen Sie uns Ihren Bedarf mit, wir nennen Ihnen Menge und Preis.'],
  'faq.lead_q' => ['tr'=>'Termin süresi ne kadar?','de'=>'Wie lang ist die Lieferzeit?'],
  'faq.lead_a' => ['tr'=>'İlk siparişlerde numune dahil genellikle 6–10 hafta; tekrar siparişlerde bu süre kısalır. Kesin termin, ürün ve adet netleştiğinde verilir.','de'=>'Bei Erstbestellungen inklusive Bemusterung meist 6–10 Wochen; Nachbestellungen gehen schneller. Der genaue Termin wird nach Klärung von Produkt und Menge genannt.'],
  'faq.sample_q' => ['tr'=>'Numune gönderiyor musunuz?','de'=>'Versenden Sie Muster?'],
  'faq.sample_a' => ['tr'=>'Evet. Standart koleksiyondan numune ve özel üretimler için strike-off / seri üretim öncesi numune sağlıyoruz. Numune ve kargo koşulları talep üzerine paylaşılır.','de'=>'Ja. Muster aus der Standardkollektion sowie Strike-off / Pre-Production-Muster für Sonderanfertigungen. Muster- und Versandbedingungen auf Anfrage.'],
  'faq.cert_q' => ['tr'=>'Hangi sertifikalara sahipsiniz?','de'=>'Welche Zertifikate haben Sie?'],
  'faq.cert_a' => ['tr'=>'Ürün ve süreçlerimiz için OEKO-TEX® ve GOTS belgelendirmesi talep üzerine sağlanır. Malzeme tarafında sertifikalı ve izlenebilir uzun elyaflı pamuk kullanıyoruz.','de'=>'OEKO-TEX®- und GOTS-Zertifizierung für Produkte und Prozesse auf Anfrage. Bei den Materialien verwenden wir zertifizierte, rückverfolgbare langstapelige Baumwolle.'],
  'faq.custom_q' => ['tr'=>'Özel üretim / private label yapıyor musunuz?','de'=>'Fertigen Sie Eigenmarken / Private Label?'],
  'faq.custom_a' => ['tr'=>'Evet. Ebat, renk, gramaj, bordür, nakış, jakar ve (promosyon grubunda) baskı ile markanıza özel üretim yapıyoruz. İplikten son dikişe kadar tüm süreç bizde.','de'=>'Ja. Individuelle Fertigung nach Größe, Farbe, Grammatur, Bordüre, Stickerei, Jacquard und (bei der Werbegruppe) Druck. Der gesamte Prozess vom Garn bis zur letzten Naht liegt bei uns.'],
  'faq.ship_q' => ['tr'=>'Nereye sevkiyat yapıyorsunuz?','de'=>'Wohin liefern Sie?'],
  'faq.ship_a' => ['tr'=>'Türkiye ve yurt dışına; perakende ve otel/kontrat müşterilerine sevkiyat yapıyoruz. Merkez ofisimiz Bursa, fabrikamız Denizli\'dedir.','de'=>'In die Türkei und ins Ausland; an Handels- und Hotel-/Objektkunden. Hauptbüro in Bursa, Werk in Denizli.'],
];
foreach ($ui as $key => $byLang) {
    foreach ($byLang as $code => $v) {
        DB::query('INSERT INTO translations (language_id,`key`,`value`) VALUES (?,?,?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)', [$L[$code], $key, $v]);
    }
}
echo "UI strings updated.\n";

/* -------------------------------------------------------------------------
 * 6. Blog articles (SEO)
 * ---------------------------------------------------------------------- */
$hasBlog = (int) DB::value("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'posts'") === 1;
if ($hasBlog) {

    $covers = DB::all("SELECT path FROM product_images ORDER BY RAND() LIMIT 12");
    $covers = array_column($covers, 'path');
    $ci = 0;
    $nextCover = function () use ($covers, &$ci) { return $covers ? $covers[$ci++ % count($covers)] : ''; };

    $articles = require BASE_DIR . '/database/blog_articles.php';

    foreach ($articles as $a) {
        // keyed by en slug to detect existing
        $marker = $a['tr']['slug'];
        $existing = DB::value('SELECT post_id FROM post_translations WHERE slug = ? LIMIT 1', [$marker]);
        if ($existing) {
            $pid = (int) $existing;
        } else {
            $pid = DB::insert('posts', [
                'cover_image'  => $nextCover(),
                'author'       => 'Zoen Tekstil',
                'is_active'    => 1,
                'published_at' => $a['date'] . ' 09:00:00',
                'created_at'   => $a['date'] . ' 09:00:00',
                'updated_at'   => date('Y-m-d H:i:s'),
            ]);
        }
        foreach (['tr', 'en', 'de'] as $code) {
            $d = $a[$code];
            $payload = [
                'title'            => $d['title'],
                'slug'             => $d['slug'],
                'excerpt'          => $d['excerpt'],
                'body'             => $d['body'],
                'meta_title'       => $d['title'] . ' | Zoen Tekstil',
                'meta_description' => $d['excerpt'],
            ];
            $ex = DB::one('SELECT id FROM post_translations WHERE post_id = ? AND language_id = ?', [$pid, $L[$code]]);
            if ($ex) { DB::update('post_translations', $payload, 'id = :id', ['id' => $ex['id']]); }
            else { DB::insert('post_translations', $payload + ['post_id' => $pid, 'language_id' => $L[$code]]); }
        }
    }
    DB::query('INSERT INTO settings (`key`,`value`) VALUES ("blog_meta_description",?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)',
        ['Guides on towel GSM, cotton types, hotel textile standards, private-label manufacturing and textile certifications from Zoen Tekstil, Denizli.']);
    echo "Blog articles seeded (" . count($articles) . ").\n";
} else {
    echo "Blog tables not found — run database/migrations/002_blog.sql first.\n";
}

echo "\nDone. Reload the site.\n";
