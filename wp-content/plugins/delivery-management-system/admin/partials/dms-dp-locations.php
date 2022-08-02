<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/admin/partials
 */

//Empty array to store locations
$locations = [];
global $wpdb;
$user = wp_get_current_user();
$table_name = $wpdb->prefix . "dms_orders";
$orders = $wpdb->get_results("SELECT * FROM " . $table_name . " WHERE delivery_personnel='" . $user->user_login . "' AND delivery_status = 'In Transit'");
foreach ($orders as $order) {
    //push the array to the locations array
    array_push($locations, geocode($order->postal_code));
}
function geocode($address)
{
    $queryString = http_build_query([
        'searchVal' => $address,
        'returnGeom' => 'Y',
        'getAddrDetails' => 'Y'
    ]);
    $ch = curl_init(sprintf('%s&%s', 'https://developers.onemap.sg/commonapi/search?', $queryString));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $json = curl_exec($ch);
    curl_close($ch);
    $apiResult = json_decode($json, true);
    //store extract values from API response
    $formatted_address = $apiResult['results'][0]['ADDRESS'];
    $latitude = $apiResult['results'][0]['LATITUDE'];
    $longitude = $apiResult['results'][0]['LONGITUDE'];
    //return all in one location array
    return array($formatted_address, $latitude, $longitude);
}
?>


<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<script src="https://unpkg.com/axios/dist/axios.min.js"></script>
<div class="wrap">
    <h1>Order Locations</h1>
    <?php
    $allData = json_encode($locations);
    echo '<div id="allData">' . $allData . '</div>';
    ?>
    <div id="map"></div>
</div>

<script>
    var map;
    var geocoder;

    //load the google map
    function loadMap() {
        map = new google.maps.Map(document.getElementById('map'), {
            zoom: 12,
            center: new google.maps.LatLng(1.3521, 103.8198),
            mapTypeId: google.maps.MapTypeId.ROADMAP
        });

        var marker = new google.maps.Marker({
            map: map
        });

        //retrieve the locations array 
        var allData = JSON.parse(document.getElementById('allData').innerHTML);
        showAllLocations(allData)
    }

    function showAllLocations(locations) {
        var infowindow = new google.maps.InfoWindow();
        var marker, i;
        for (i = 0; i < locations.length; i++) {
            marker = new google.maps.Marker({
                //based on the long and lat show the marker
                position: new google.maps.LatLng(locations[i][1], locations[i][2]),
                map: map
            });

            google.maps.event.addListener(marker, 'click', (function(marker, i) {
                return function() {
                    //show the label of location
                    infowindow.setContent(locations[i][0]);
                    infowindow.open(map, marker);
                }
            })(marker, i));
        }
    }
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC7zqC7d3_gVvXTuXoOujvGOA5dT2bhP1s&callback=loadMap"></script>