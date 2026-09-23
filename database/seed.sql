-- Gürdoğa Kooperatifi — seed data (safe to run once on a fresh schema)
SET NAMES utf8mb4;

INSERT INTO languages (id, code, name, locale, is_default, is_active, sort) VALUES
  (1, 'tr', 'Türkçe', 'tr_TR', 1, 1, 0);

INSERT INTO categories (id, parent_id, slug, image, sort, is_active, created_at, updated_at) VALUES
  (1, NULL, 'zeytinyagi',   '', 1, 1, NOW(), NOW()),
  (2, NULL, 'bal-pekmez',   '', 2, 1, NOW(), NOW()),
  (3, NULL, 'recel-tursu',  '', 3, 1, NOW(), NOW()),
  (4, NULL, 'sut-urunleri', '', 4, 1, NOW(), NOW());

INSERT INTO category_translations (category_id, language_id, name, description, meta_title, meta_description) VALUES
  (1,1,'Zeytinyağı','Kooperatif ortaklarımızın zeytinliklerinden soğuk sıkım zeytinyağı.','Zeytinyağı','Gürdoğa Kooperatifi soğuk sıkım zeytinyağı çeşitleri.'),
  (2,1,'Bal & Pekmez','Doğal bal ve geleneksel yöntemlerle üretilen pekmez çeşitleri.','Bal & Pekmez','Gürdoğa Kooperatifi bal ve pekmez ürünleri.'),
  (3,1,'Reçel & Turşu','Mevsiminde toplanan meyve ve sebzelerden reçel ve turşu.','Reçel & Turşu','Gürdoğa Kooperatifi reçel ve turşu ürünleri.'),
  (4,1,'Süt Ürünleri','Yöresel süt ürünleri: peynir, yoğurt ve tereyağı.','Süt Ürünleri','Gürdoğa Kooperatifi süt ürünleri.');

INSERT INTO pages (id, slug, template, is_active, sort, created_at, updated_at) VALUES
  (1, 'about',            'default', 1, 1, NOW(), NOW()),
  (2, 'production',       'default', 1, 2, NOW(), NOW()),
  (3, 'points-of-sale',   'sales-points', 1, 3, NOW(), NOW());

INSERT INTO page_translations (page_id, language_id, title, body, meta_title, meta_description, og_image) VALUES
  (1,1,'Gürdoğa Kooperatifi Hakkında',
      '<p>Gürdoğa Kooperatifi; bölgemizdeki üretici ortaklarımızın yetiştirdiği zeytinyağı, bal, pekmez, reçel, turşu ve süt ürünlerini doğal ve katkısız şekilde sizlere ulaştıran bir tarımsal kalkınma kooperatifidir. Bu metni yönetim panelinden düzenleyebilirsiniz.</p>',
      'Hakkımızda','Gürdoğa Kooperatifi — doğal ve yerel ürünler üreten tarımsal kalkınma kooperatifi.',''),
  (2,1,'Üretim',
      '<p>Ürünlerimiz, kooperatif ortağı üreticilerimizin tarla ve bahçelerinden geleneksel yöntemlerle, katkı maddesi kullanılmadan üretilir. Üretim sürecinizi burada anlatabilirsiniz.</p>',
      'Üretim','Gürdoğa Kooperatifi üretim süreci: doğal ve geleneksel yöntemlerle üretim.',''),
  (3,1,'Satış Noktaları',
      '<p>Ürünlerimizi aşağıdaki satış noktalarımızdan temin edebilir, ya da ürün sayfalarındaki "Satın Al" bağlantısı ile Lezzet Kurye üzerinden sipariş verebilirsiniz. Satış noktalarınızın adres ve iletişim bilgilerini burada listeleyin.</p>',
      'Satış Noktaları','Gürdoğa Kooperatifi ürünlerini bulabileceğiniz satış noktaları.','');

-- Header / footer menu
INSERT INTO menu_items (id, location, type, ref_id, url, sort) VALUES
  (1,'header','home',NULL,'',0),
  (2,'header','products',NULL,'',1),
  (3,'header','page',1,'',2),
  (4,'header','page',2,'',3),
  (5,'header','page',3,'',4),
  (6,'header','contact',NULL,'',5),
  (7,'footer','page',1,'',0),
  (8,'footer','page',3,'',1),
  (9,'footer','contact',NULL,'',2);

INSERT INTO menu_item_translations (menu_item_id, language_id, label) VALUES
  (1,1,'Anasayfa'),
  (2,1,'Ürünler'),
  (3,1,'Hakkımızda'),
  (4,1,'Üretim'),
  (5,1,'Satış Noktaları'),
  (6,1,'İletişim'),
  (7,1,'Hakkımızda'),
  (8,1,'Satış Noktaları'),
  (9,1,'İletişim');

INSERT INTO settings (`key`,`value`) VALUES
  ('site_name','Gürdoğa Kooperatifi'),
  ('contact_email',''),
  ('contact_phone',''),
  ('contact_address',''),
  ('social_linkedin',''),('social_instagram',''),('social_facebook',''),('social_youtube',''),
  ('ga4_id',''),('gsc_verification',''),
  ('quote_recipients',''),
  ('marquee_items','Zeytinyağı, Bal & Pekmez, Reçel & Turşu, Süt Ürünleri, Doğal Üretim, Kooperatif Güvencesi, Lezzet Kurye ile Satın Al'),
  ('home_meta_title','Gürdoğa Kooperatifi — Doğal ve Yerel Ürünler'),
  ('home_meta_description','Gürdoğa Kooperatifi; zeytinyağı, bal, pekmez, reçel, turşu ve süt ürünlerini doğal ve katkısız şekilde üretir.'),
  ('products_meta_description','Gürdoğa Kooperatifi ürünlerini keşfedin: zeytinyağı, bal & pekmez, reçel & turşu, süt ürünleri.'),
  ('contact_meta_description','Gürdoğa Kooperatifi ile iletişime geçin.'),
  ('default_og_image',''),('logo',''),('favicon',''),('robots_txt',''),('map_embed',''),
  ('topbar_note','Gürdoğa Kooperatifi · Doğal ve yerel ürünler');

INSERT INTO setting_translations (`key`, language_id, `value`) VALUES
  ('site_tagline',1,'Doğal ve yerel kooperatif ürünleri'),
  ('footer_about',1,'Gürdoğa Kooperatifi; bölge üreticilerinin zeytinyağı, bal, pekmez, reçel, turşu ve süt ürünlerini doğal ve katkısız şekilde sizlere ulaştırır.'),
  ('footer_eyebrow',1,'Gürdoğa Kooperatifi'),
  ('home_meta_title',1,'Gürdoğa Kooperatifi — Doğal ve Yerel Ürünler'),
  ('home_meta_description',1,'Gürdoğa Kooperatifi; zeytinyağı, bal, pekmez, reçel, turşu ve süt ürünlerini doğal ve katkısız şekilde üretir.'),
  ('products_meta_description',1,'Gürdoğa Kooperatifi ürünlerini keşfedin: zeytinyağı, bal & pekmez, reçel & turşu, süt ürünleri.'),
  ('contact_meta_description',1,'Gürdoğa Kooperatifi ile iletişime geçin.');

-- UI strings (Turkish)
INSERT INTO translations (language_id, `key`, `value`) VALUES
  (1,'nav.home','Anasayfa'),(1,'nav.products','Ürünler'),(1,'nav.about','Hakkımızda'),(1,'nav.contact','İletişim'),(1,'nav.quote','Teklif İste'),(1,'nav.production','Üretim'),
  (1,'products.intro','Kooperatif ürünlerimizi keşfedin.'),(1,'products.all','Tüm Ürünler'),
  (1,'product.request_quote','Bu ürün için teklif isteyin'),(1,'product.buy_now','Satın Al'),(1,'product.specs','Özellikler'),(1,'product.gallery','Galeri'),
  (1,'product.related_eyebrow','Gürdoğa Kooperatifi'),
  (1,'quote.title','Teklif İste'),(1,'quote.intro','İhtiyacınızı yazın, size özel bir teklif hazırlayalım.'),
  (1,'contact.intro','Ekibimizle iletişime geçin.'),
  (1,'form.name','Ad Soyad'),(1,'form.company','Firma'),(1,'form.email','E-posta'),(1,'form.phone','Telefon'),(1,'form.country','Ülke'),(1,'form.message','Mesaj'),(1,'form.send','Gönder'),
  (1,'form.required','Bu alan zorunludur.'),(1,'form.email_invalid','Geçerli bir e-posta girin.'),(1,'form.message_short','Lütfen biraz daha ayrıntı yazın.'),
  (1,'form.fix_errors','Lütfen aşağıdaki hataları düzeltin.'),(1,'form.thanks','Teşekkürler — en kısa sürede size döneceğiz.'),
  (1,'search.title','Arama'),(1,'search.placeholder','Ürün ara…'),(1,'search.no_results','Ürün bulunamadı.'),
  (1,'footer.rights','Tüm hakları saklıdır.'),(1,'home.featured','Öne Çıkan Ürünler'),(1,'home.categories','Ürün Grupları'),(1,'home.cta','Teklif İste'),
  (1,'common.read_more','Devamını oku'),(1,'common.back_to','Geri dön:'),(1,'error.404_title','Sayfa bulunamadı'),(1,'error.404_text','Aradığınız sayfa mevcut değil.'),
  (1,'nav.corporate','Kurumsal'),(1,'nav.production','Üretim'),
  (1,'products.items','ürün'),(1,'product.related','İlginizi çekebilir'),(1,'product.related_eyebrow','Gürdoğa Kooperatifi'),
  (1,'contact.title','Bize Ulaşın'),(1,'form.thanks_title','Teşekkürler'),
  (1,'footer.address','Adres'),(1,'footer.company','Firma'),(1,'footer.eyebrow','Gürdoğa Kooperatifi'),(1,'footer.made_by','Tasarım'),
  (1,'footer.headline_1','Doğadan sofranıza,'),(1,'footer.headline_2','emekle üretildi.'),
  (1,'common.discover','Keşfet'),(1,'common.view','Görüntüle'),(1,'common.close','Kapat'),(1,'common.skip','İçeriğe geç'),(1,'common.back_to_top','Yukarı çık'),
  (1,'home.collections_title','Her mevsim doğal ürünler'),(1,'home.cta_title','Sizin için en iyisini üretelim'),(1,'home.featured_title','Koleksiyonumuzdan seçmeler'),
  (1,'blog.eyebrow','Bilgi'),(1,'blog.title','Blog'),(1,'blog.intro','Kooperatifimizden haberler ve ürünlerimize dair yazılar.'),
  (1,'blog.empty','Henüz yazı yok.'),(1,'blog.more','Diğer yazılar'),(1,'blog.cta','Ürünlerimiz hakkında daha fazla bilgi almak ister misiniz?');

INSERT INTO sales_points (name, channel, city, district, address, phone, lat, lng, sort, is_active, created_at, updated_at) VALUES
  ('Gürdoğa Satış Mağazası', 'Kendi Mağazamız', 'Ankara', 'Çankaya', 'Örnek Mah. Örnek Cd. No:1', '', 39.9255, 32.8663, 0, 1, NOW(), NOW()),
  ('Lezzet Kurye Online Satış', 'Lezzet Kurye', '', '', 'Tüm Türkiye''ye online teslimat', '', NULL, NULL, 1, 1, NOW(), NOW());
