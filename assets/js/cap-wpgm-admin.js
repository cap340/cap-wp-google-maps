// todo: comments

(function ($) {
    $(document).ready(function () {
        const {__} = wp.i18n;

        function getCoordinates() {
            $("button[aria-controls='address']").on("click", function () {
                let inputApiKey = $("input[name='cap_wpgm_options[api_key]']");
                let apiKey = inputApiKey.val();
                let mapContainer = $("#map");

                if (!apiKey || apiKey === "") {
                    inputApiKey.attr("placeholder", __("Please provide an API key", "cap-wpgm"));
                    inputApiKey.addClass("is-error");
                    return;
                }

                mapContainer.show();
                loadScript(apiKey);
            });
        }
        getCoordinates();

        function clearInput() {
            $("input[type='text']").on("focus", function() {
                $(this).removeClass("is-success is-error");
            });
        }
        clearInput();

        let container = $(".field-theme-custom");
        if($('#theme_custom').is(':checked')) {
            container.show();
        }
        $(".field-theme input[type=radio]").change(function () {
            if (this.value === 'custom') {
                container.show();
            } else {
                container.hide();
            }
        });
    });
})(jQuery);

let map;
let marker;
let infowindow;
let geocoder;

function loadScript(apiKey) {
    let script = document.createElement("script");
    script.id = "maps-googleapis";
    script.type = "text/javascript";
    script.src = "https://maps.googleapis.com/maps/api/js?key=" + apiKey + "&callback=initMap&v=weekly";
    script.async = true;
    document.body.appendChild(script);
}

function initMap() {
    map = new google.maps.Map(document.getElementById("map"), {
        zoom: 16,
        center: {lat: -34.397, lng: 150.644},
        mapTypeControl: false,
    });
    geocoder = new google.maps.Geocoder();
    let address = document.getElementById("address").value;
    geocode({address: address});
}

function geocode(request) {
    geocoder
        .geocode(request)
        .then((result) => {
            const {results} = result;
            map.setCenter(results[0].geometry.location);
            marker = new google.maps.Marker({
                map,
            });
            marker.setPosition(results[0].geometry.location);
            marker.setMap(map);
            infowindow = new google.maps.InfoWindow({
                content: JSON.stringify(result, null, 2),
            });
            marker.addListener("click", () => {
                infowindow.open({
                    anchor: marker,
                    map,
                    shouldFocus: false,
                });
            });

            document.getElementById("lat").value = results[0].geometry.location.lat();
            document.getElementById("lat").classList.add("is-success");
            document.getElementById("lng").value = results[0].geometry.location.lng();
            document.getElementById("lng").classList.add("is-success");

            return results;
        })
        .catch((e) => {
            alert("Geocode was not successful for the following reason: " + e);
        });
}
