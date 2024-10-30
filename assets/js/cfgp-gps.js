/**
 * GPS for CF Geo Plugin
 *
 * @link              http://cfgeoplugin.com/
 * @since             1.0.0
 * @version           1.0.5
 * @package           CF_Geoplugin_GPS
 * @autor             INFINITUM FORM
 * @license           GPL-2.0+
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

;(function($){
    const gpsPreloader = $('#cf-geoplugin-gps-preloader');

    const getCookie = (cname) => {
        const name = cname + "=";
        const decodedCookie = decodeURIComponent(document.cookie);
        const ca = decodedCookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i].trim();
            if (c.indexOf(name) === 0) {
                return c.substring(name.length, c.length);
            }
        }
        return null;
    };

    const handleGeoData = (data, latitude, longitude) => {
        if (data.status !== 'OK') {
            handleError(data.status, data.error_message);
            return;
        }

        const geo = {};
        data.results[0].address_components.forEach(component => {
            const key = component.types[0];
            geo[key] = {
                long_name: component.long_name,
                short_name: component.short_name
            };
        });

        if (geo.country) {
            geo.countryCode = geo.country.short_name;
            geo.countryName = geo.country.long_name;
        }

        if (geo.locality) {
            geo.cityName = geo.locality.long_name;
            geo.cityCode = geo.locality.short_name;
        }

        if (geo.administrative_area_level_1) {
            geo.region = geo.administrative_area_level_1.long_name;
            geo.state = geo.administrative_area_level_1.long_name;
            geo.regionName = geo.administrative_area_level_1.long_name;
        }

        if (geo.administrative_area_level_2) {
            geo.district = geo.administrative_area_level_2.long_name;
        }

        if (geo.political) {
            geo.region = geo.political.long_name;
            geo.state = geo.political.long_name;
            geo.regionName = geo.political.long_name;
        }

        if (geo.postal_code) {
            geo.zip = geo.postal_code.long_name;
        }

        if (geo.route) {
            geo.street = geo.route.long_name;
        }

        if (geo.street_number) {
            geo.street_number = geo.street_number.long_name;
        }

        if (data.results[0].formatted_address) {
            geo.address = data.results[0].formatted_address;
        }

        if (data.results[0].geometry && data.results[0].geometry.location) {
            geo.latitude = data.results[0].geometry.location.lat;
            geo.longitude = data.results[0].geometry.location.lng;
            geo.place_id = data.results[0].geometry.place_id;
        } else {
            geo.latitude = latitude;
            geo.longitude = longitude;
        }

        $.post(CFGEO_GPS.ajax_url, {
            action: 'cf_geoplugin_gps_set',
            data: geo
        }).done(response => {
            if (response.success) {
                clearCacheAndReload();
            }
        });
    };

    const handleError = (status, errorMessage) => {
        const errorMessages = {
            'ZERO_RESULTS': CFGEO_GPS.label.ZERO_RESULTS,
            'OVER_DAILY_LIMIT': CFGEO_GPS.label.OVER_DAILY_LIMIT,
            'OVER_QUERY_LIMIT': CFGEO_GPS.label.OVER_QUERY_LIMIT,
            'REQUEST_DENIED': CFGEO_GPS.label.REQUEST_DENIED,
            'INVALID_REQUEST': CFGEO_GPS.label.INVALID_REQUEST,
            'UNKNOWN_ERROR': CFGEO_GPS.label.DATA_UNKNOWN_ERROR
        };

        const returns = errorMessages[status] || null;

        if (returns) {
            if (errorMessage) {
                console.error(CFGEO_GPS.label.google_geocode.replace(/%s/g, errorMessage));
            } else {
                console.info(CFGEO_GPS.label.google_geocode.replace(/%s/g, returns));
            }

            if (gpsPreloader.length > 0 && getCookie('cfgp_gps') != 1) {
                gpsPreloader.addClass('hidden');
            }
        }
    };

    const sendPosition = (position) => {
        const latitude = position.coords.latitude;
        const longitude = position.coords.longitude;

        if (gpsPreloader.length > 0 && getCookie('cfgp_gps') != 1) {
            gpsPreloader.removeClass('hidden');
        } else if (gpsPreloader.length > 0 && getCookie('cfgp_gps') == 1) {
            gpsPreloader.remove();
        }

        $.get('https://maps.googleapis.com/maps/api/geocode/json', {
            key: CFGEO_GPS.key,
            language: CFGEO_GPS.language,
            latlng: `${latitude},${longitude}`
        }).done(data => handleGeoData(data, latitude, longitude))
          .fail(() => {
              if (gpsPreloader.length > 0 && getCookie('cfgp_gps') != 1) {
                  gpsPreloader.addClass('hidden');
              }
          });
    };

    const displayError = (error) => {
        const errorMessages = {
            [error.PERMISSION_DENIED]: CFGEO_GPS.label.PERMISSION_DENIED,
            [error.POSITION_UNAVAILABLE]: CFGEO_GPS.label.POSITION_UNAVAILABLE,
            [error.TIMEOUT]: CFGEO_GPS.label.TIMEOUT,
            [error.UNKNOWN_ERROR]: CFGEO_GPS.label.UNKNOWN_ERROR
        };

        const returns = errorMessages[error.code] || null;

        if (returns) {
            console.error(CFGEO_GPS.label.google_geocode.replace(/%s/g, returns));
            if (gpsPreloader.length > 0 && getCookie('cfgp_gps') != 1) {
                gpsPreloader.addClass('hidden');
            }
        }
    };

    const getLocation = () => {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(sendPosition, displayError);
        } else {
            console.log(CFGEO_GPS.label.google_geocode);

            if (gpsPreloader.length > 0 && getCookie('cfgp_gps') != 1) {
                gpsPreloader.addClass('hidden');
            }
        }
    };

    const clearCacheAndReload = () => {
        if (typeof caches !== 'undefined') {
            caches.keys().then(keyList => {
                if (typeof Promise !== 'undefined') {
                    Promise.all(keyList.map(key => caches.delete(key)));
                }
            });
        }

        const href = window.location.href;
        const salt = Array.from({ length: 32 }, () => "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"[Math.floor(Math.random() * 62)]).join('');

        if (href.indexOf('?') > -1) {
            window.location.href = `${href}&gps=1&salt=${salt}`;
        } else {
            window.location.href = `${href}?gps=1&salt=${salt}`;
        }

        window.history.forward(1);
    };

    getLocation();
}(jQuery || window.jQuery || Zepto || window.Zepto));
