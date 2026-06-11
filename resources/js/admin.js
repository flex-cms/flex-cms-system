import "@fortawesome/fontawesome-free/css/all.min.css";

import 'ace-builds/src-noconflict/ace.js';
import 'ace-builds/src-noconflict/mode-html.js';
import 'ace-builds/src-noconflict/theme-monokai.js';
import 'ace-builds/src-noconflict/ext-beautify.js';
import 'tom-select/dist/css/tom-select.bootstrap5.css';

import('ace-builds').then((ace) => {
    window.ace = ace;
});

import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.min.css";
import { Bulgarian } from "flatpickr/dist/l10n/bg.js";

window.flatpickr = flatpickr;
window.Bulgarian = Bulgarian;

import "../css/app.css";
import Alpine from "alpinejs";
import collapse from '@alpinejs/collapse';

import sidebar from "./admin/components/sidebar.js";
import uiSection from "./admin/components/ui-section.js";
import alertComponent from './admin/components/alert.js';
import updater from './admin/components/updater.js';
import deleteManager from './admin/components/deleteManager.js';
import tableManager from './admin/components/table-manager.js';
import pluginManager from './admin/components/plugin-manager';
import codeEditor from "./admin/components/code-editor.js";
import selectComponent from './admin/components/select.js';
import initDatepicker from './admin/components/datepicker';
import passwordStrength from './admin/components/password-strength.js';

window.Alpine = Alpine;
Alpine.plugin(collapse);

Alpine.data("sidebar", sidebar);
Alpine.data("uiSection", uiSection);
Alpine.data('alertComponent', alertComponent);
Alpine.data('updater', updater);
Alpine.data('deleteManager', deleteManager);
Alpine.data('tableManager', tableManager);
Alpine.data('pluginManager', pluginManager);
Alpine.data('codeEditor', codeEditor);
Alpine.data('tomSelect', selectComponent);
Alpine.data('datepicker', initDatepicker);
Alpine.data('passwordStrength', passwordStrength);

Alpine.start();
