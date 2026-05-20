import "@fortawesome/fontawesome-free/css/all.min.css";

import "../css/app.css";
import Alpine from "alpinejs";
import collapse from '@alpinejs/collapse';

import sidebar from "./admin/components/sidebar.js";
import uiSection from "./admin/components/ui-section.js";
import alertComponent from './admin/components/alert.js';
import updater from './admin/components/updater.js';

window.Alpine = Alpine;
Alpine.plugin(collapse);

Alpine.data("sidebar", sidebar);
Alpine.data("uiSection", uiSection);
Alpine.data('alertComponent', alertComponent);
Alpine.data('updater', updater);

Alpine.start();