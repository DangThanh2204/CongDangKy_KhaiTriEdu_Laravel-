import './bootstrap';
import * as bootstrap from 'bootstrap';
// Expose so inline scripts (admin sidebar tooltips, modal close, etc.) can
// reach the Bootstrap namespace via window.bootstrap.
window.bootstrap = bootstrap;

// Custom JavaScript
import './custom';