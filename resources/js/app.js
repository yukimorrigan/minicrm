require('./bootstrap');
require('bootstrap');
require('admin-lte');

import $ from 'jquery';
global.jQuery = $;
global.$ = $;

require('admin-lte/plugins/datatables/jquery.dataTables.min');
require('admin-lte/plugins/datatables-buttons/js/dataTables.buttons.min');
require('admin-lte/plugins/datatables-buttons/js/buttons.bootstrap4.min');
import bsCustomFileInput from 'admin-lte/plugins/bs-custom-file-input/bs-custom-file-input.min';
$(function () {
    bsCustomFileInput.init();
});

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();
