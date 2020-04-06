const $ = require('jquery');
require('@popperjs/core');
require('bootstrap-select');

$.fn.selectpicker.Constructor.BootstrapVersion = '4';
$('select').selectpicker();