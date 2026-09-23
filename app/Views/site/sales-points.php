<?php
/** @var array $page @var string $title @var array $crumbs @var array $salesPoints */
$pointsForJs = array_values(array_map(function (array $p): array {
    return [
        'id'       => (int) $p['id'],
        'channel'  => $p['channel'] ?: 'Diğer',
        'name'     => $p['name'],
        'city'     => $p['city'],
        'district' => $p['district'],
        'address'  => $p['address'],
        'phone'    => $p['phone'],
        'lat'      => $p['lat'] !== null ? (float) $p['lat'] : null,
        'lng'      => $p['lng'] !== null ? (float) $p['lng'] : null,
    ];
}, $salesPoints ?? []));
?>
<div class="sales-wrapper">


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div class="sales-header">

        <h1>Satış Kanalı</h1>

        <p>
            Alışveriş tercihinize göre keşfedin
        </p>

    </div>


    <!-- =====================================================
         CHANNELS
    ====================================================== -->

    <div class="channel-wrapper">

        <button
            class="channel-arrow left"
            id="channelPrev"
        >
            ←
        </button>


        <div
            class="channel-slider"
            id="channelSlider"
        >
        </div>


        <button
            class="channel-arrow right"
            id="channelNext"
        >
            →
        </button>

    </div>


    <!-- =====================================================
         FILTER
    ====================================================== -->

    <div class="filter-bar">

        <div class="search-box">

            <span class="search-icon">
                ⌕
            </span>

            <input
                type="text"
                id="searchInput"
                placeholder="Mağaza, şehir veya adres ara..."
            >

        </div>


        <select
            class="city-select"
            id="cityFilter"
        >

            <option value="all">
                Tüm şehirler
            </option>

        </select>

    </div>


    <!-- =====================================================
         CONTENT
    ====================================================== -->

    <div class="sales-content">


        <!-- LEFT -->

        <div class="sales-list-wrapper">

            <div class="list-header">

                <h2 class="list-title">
                    Tüm Satış Noktaları
                </h2>

                <span
                    class="result-count"
                    id="resultCount"
                >
                    0 sonuç
                </span>

            </div>


            <div
                class="sales-list"
                id="salesList"
            >
            </div>

        </div>


        <!-- MAP -->

        <div
            class="map-wrapper"
            id="mapWrapper"
        >

            <div class="map-switch">

                <button
                    id="listMapBtn"
                    class="active"
                >
                    ▦ Liste + Harita
                </button>

                <button
                    id="mapOnlyBtn"
                >
                    ◉ Sadece Harita
                </button>

            </div>


            <div id="salesMap"></div>

        </div>


    </div>

</div>


<!-- =========================================================
     LEAFLET
========================================================= -->

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>


<script>

/* ============================================================
   SATIŞ NOKTALARI

   Panelden (/admin/sales-points) yönetilir.
============================================================ */

const salesPoints = <?= json_encode($pointsForJs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

/* ============================================================
   SATIŞ KANALLARI

   Satış noktalarındaki "channel" alanından otomatik türetilir.
============================================================ */

const channelNames = [...new Set(salesPoints.map(p => p.channel))].sort((a, b) => a.localeCompare(b, 'tr'));

const channels = [
    { id: "all", name: "Tümü" },
    ...channelNames.map(name => ({ id: name, name }))
];


/* ============================================================
   GLOBAL
============================================================ */

let activeChannel = "all";

let activePoint = null;

let markers = {};

let markerLayer;


/* ============================================================
   MAP
============================================================ */

const map = L.map("salesMap", {

    zoomControl: true,

    scrollWheelZoom: true

}).setView(
    [39.0, 35.0],
    6
);


L.tileLayer(
    "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
    {
        maxZoom: 19,

        attribution:
            '&copy; OpenStreetMap contributors'
    }
).addTo(map);


markerLayer = L.layerGroup().addTo(map);


/* ============================================================
   CHANNEL RENDER
============================================================ */

const channelSlider =
    document.getElementById("channelSlider");


function renderChannels() {

    channelSlider.innerHTML = "";

    channels.forEach(channel => {

        const count =
            channel.id === "all"

            ? salesPoints.length

            : salesPoints.filter(
                item =>
                    item.channel === channel.id
            ).length;


        const card =
            document.createElement("div");

        card.className =
            "channel-card" +
            (
                activeChannel === channel.id
                    ? " active"
                    : ""
            );


        card.dataset.channel =
            channel.id;


        card.innerHTML = `

            <div class="channel-logo">

                <div class="channel-logo-text">
                    ${count}
                </div>

            </div>

            <div class="channel-name">
                ${channel.name}
            </div>

            ${
                activeChannel === channel.id

                ? `
                    <div class="channel-selected">
                        Seçili
                    </div>
                  `

                : ""
            }

        `;


        card.addEventListener(
            "click",
            () => {

                activeChannel =
                    channel.id;

                renderChannels();

                renderSales();

            }
        );


        channelSlider.appendChild(card);

    });

}


/* ============================================================
   CITY FILTER
============================================================ */

const cityFilter =
    document.getElementById("cityFilter");


function renderCities() {

    const cities =
        [
            ...new Set(
                salesPoints.map(
                    item => item.city
                )
            )
        ]
        .sort();


    cities.forEach(city => {

        const option =
            document.createElement("option");

        option.value = city;

        option.textContent = city;

        cityFilter.appendChild(option);

    });

}


/* ============================================================
   FILTER
============================================================ */

const searchInput =
    document.getElementById("searchInput");


function getFilteredPoints() {

    const search =
        searchInput.value
            .trim()
            .toLocaleLowerCase("tr-TR");


    const city =
        cityFilter.value;


    return salesPoints.filter(point => {

        const channelMatch =
            activeChannel === "all" ||
            point.channel === activeChannel;


        const cityMatch =
            city === "all" ||
            point.city === city;


        const searchMatch =
            !search ||

            point.name
                .toLocaleLowerCase("tr-TR")
                .includes(search)

            ||

            point.city
                .toLocaleLowerCase("tr-TR")
                .includes(search)

            ||

            point.district
                .toLocaleLowerCase("tr-TR")
                .includes(search)

            ||

            point.address
                .toLocaleLowerCase("tr-TR")
                .includes(search);


        return (
            channelMatch &&
            cityMatch &&
            searchMatch
        );

    });

}


/* ============================================================
   SALES LIST
============================================================ */

const salesList =
    document.getElementById("salesList");

const resultCount =
    document.getElementById("resultCount");


function renderSales() {

    const points =
        getFilteredPoints();


    salesList.innerHTML = "";


    resultCount.textContent =
        points.length + " sonuç";


    /*
     * Markerları temizle
     */

    markerLayer.clearLayers();

    markers = {};


    if (!points.length) {

        salesList.innerHTML = `

            <div class="empty-result">

                <strong>
                    Satış noktası bulunamadı
                </strong>

                Arama kriterlerinizi
                değiştirmeyi deneyin.

            </div>

        `;

        return;

    }


    points.forEach(point => {

        createSalesCard(point);

        if (point.lat !== null && point.lng !== null) {
            createMarker(point);
        }

    });

}


/* ============================================================
   SALES CARD
============================================================ */

function createSalesCard(point) {

    const card =
        document.createElement("div");


    card.className =
        "sales-card" +

        (
            activePoint === point.id
                ? " active"
                : ""
        );


    card.dataset.id =
        point.id;


    const phoneHTML =
        point.phone

        ? `

            <a
                class="sales-phone"
                href="tel:${point.phone}"
                onclick="event.stopPropagation()"
            >

                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path
                        d="M22 16.92v3
                           a2 2 0 0 1
                           -2.18 2
                           19.79 19.79
                           0 0 1
                           -8.63-3.07
                           19.5 19.5
                           0 0 1
                           -6-6
                           A19.79 19.79
                           0 0 1
                           2.12 4.18
                           A2 2 0 0 1
                           4.11 2h3
                           a2 2 0 0 1
                           2 1.72
                           12.4 12.4
                           0 0 0
                           .7 2.81
                           2 2 0 0 1
                           -.45 2.11
                           L8.09 9.91
                           a16 16
                           0 0 0
                           6 6
                           l1.27-1.27
                           a2 2 0 0 1
                           2.11-.45
                           12.4 12.4
                           0 0 0
                           2.81.7
                           A2 2 0 0 1
                           22 16.92z"
                    />
                </svg>

                ${point.phone}

            </a>

          `

        : "";


    card.innerHTML = `

        <div class="sales-card-top">

            <div class="store-icon">

                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >

                    <path
                        d="M4 10v10h16V10"
                    />

                    <path
                        d="M3 10
                           L5 4
                           h14
                           l2 6"
                    />

                    <path
                        d="M3 10
                           c0 1.7
                           1.3 3
                           3 3
                           s3-1.3
                           3-3
                           c0 1.7
                           1.3 3
                           3 3
                           s3-1.3
                           3-3
                           c0 1.7
                           1.3 3
                           3 3
                           s3-1.3
                           3-3"
                    />

                    <path
                        d="M9 20v-5h6v5"
                    />

                </svg>

            </div>


            <div class="sales-info">

                <h3 class="sales-name">
                    ${point.name}
                </h3>


                <div class="sales-location">

                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >

                        <path
                            d="M20 10
                               c0 5-8 11-8 11
                               S4 15 4 10
                               a8 8 0 1 1
                               16 0z"
                        />

                        <circle
                            cx="12"
                            cy="10"
                            r="2.5"
                        />

                    </svg>

                    ${point.district} · ${point.city}

                </div>

            </div>


            ${phoneHTML}

        </div>


        <div class="sales-address">

            ${point.address}

        </div>


        <a
            href="${
                point.lat !== null && point.lng !== null
                    ? `https://www.google.com/maps/dir/?api=1&destination=${point.lat},${point.lng}`
                    : `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(point.address + ' ' + point.district + ' ' + point.city)}`
            }"
            target="_blank"
            rel="noopener"
            class="direction-btn"
            onclick="event.stopPropagation()"
        >

            <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
            >

                <path
                    d="M12 2
                       L20 6
                       L12 22
                       L4 18
                       Z"
                />

                <path
                    d="M12 6v12"
                />

            </svg>

            Yol Tarifi

        </a>

    `;


    card.addEventListener(
        "click",
        () => {

            selectPoint(point);

        }
    );


    salesList.appendChild(card);

}


/* ============================================================
   MARKER
============================================================ */

function createMarker(point) {

    const icon =
        L.divIcon({

            className: "",

            html:
                `<div class="custom-marker"></div>`,

            iconSize:
                [42, 42],

            iconAnchor:
                [21, 42],

            popupAnchor:
                [0, -38]

        });


    const marker =
        L.marker(
            [point.lat, point.lng],
            {
                icon: icon
            }
        );


    marker.bindPopup(`

        <div class="popup-title">
            ${point.name}
        </div>

        <div class="popup-city">
            ${point.district} · ${point.city}
        </div>

        <div class="popup-address">
            ${point.address}
        </div>

    `);


    marker.on(
        "click",
        () => {

            selectPoint(
                point,
                false
            );

        }
    );


    marker.addTo(markerLayer);


    markers[point.id] =
        marker;

}


/* ============================================================
   SELECT POINT
============================================================ */

function selectPoint(
    point,
    openPopup = true
) {

    activePoint =
        point.id;


    /*
     * Kartları güncelle
     */

    document
        .querySelectorAll(".sales-card")
        .forEach(card => {

            card.classList.toggle(
                "active",
                Number(card.dataset.id)
                    === point.id
            );

        });


    /*
     * Haritaya git
     */

    if (point.lat !== null && point.lng !== null) {
        map.flyTo(
            [point.lat, point.lng],
            14,
            {
                duration: .7
            }
        );
    }


    if (
        openPopup &&
        markers[point.id]
    ) {

        markers[point.id]
            .openPopup();

    }


    /*
     * Mobilde kart görünür olsun
     */

    const card =
        document.querySelector(
            `.sales-card[data-id="${point.id}"]`
        );


    if (card) {

        card.scrollIntoView({
            behavior: "smooth",
            block: "nearest"
        });

    }

}


/* ============================================================
   SEARCH
============================================================ */

searchInput.addEventListener(
    "input",
    renderSales
);


cityFilter.addEventListener(
    "change",
    renderSales
);


/* ============================================================
   CHANNEL SLIDER
============================================================ */

document
    .getElementById("channelPrev")
    .addEventListener(
        "click",
        () => {

            channelSlider.scrollBy({
                left: -400,
                behavior: "smooth"
            });

        }
    );


document
    .getElementById("channelNext")
    .addEventListener(
        "click",
        () => {

            channelSlider.scrollBy({
                left: 400,
                behavior: "smooth"
            });

        }
    );


/* ============================================================
   LIST + MAP / MAP ONLY
============================================================ */

const listMapBtn =
    document.getElementById("listMapBtn");

const mapOnlyBtn =
    document.getElementById("mapOnlyBtn");


listMapBtn.addEventListener(
    "click",
    () => {

        document
            .querySelector(".sales-list-wrapper")
            .style.display = "";

        listMapBtn.classList.add("active");

        mapOnlyBtn.classList.remove("active");

        setTimeout(
            () => map.invalidateSize(),
            100
        );

    }
);


mapOnlyBtn.addEventListener(
    "click",
    () => {

        document
            .querySelector(".sales-list-wrapper")
            .style.display = "none";

        mapOnlyBtn.classList.add("active");

        listMapBtn.classList.remove("active");

        setTimeout(
            () => map.invalidateSize(),
            100
        );

    }
);


/* ============================================================
   INITIALIZE
============================================================ */

renderCities();

renderChannels();

renderSales();

</script>
