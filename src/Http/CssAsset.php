<?php

namespace Mikomagni\SimpleLikes\Http;

class CssAsset
{
    public static function generateDynamicCss()
    {
        return '
/* Simple Likes Widget Styles */

.text-tiny {
    font-size: 10px !important;
}

.simple-likes-table-fixed {
    table-layout: fixed !important;
    width: 100% !important;
}

.simple-likes-real-likes {
    color: #10b981;
}
.simple-likes-presets-likes {
    color: #f97316;
}
.simple-likes-user-likes {
    color: #3b82f6;
}
.simple-likes-guest-likes {
    color: #8b5cf6;
}

/* Default mobile-first approach */
.simple-likes-mobile-hide {
    display: none !important;
}

th.simple-likes-mobile-hide,
td.simple-likes-mobile-hide {
    display: none !important;
}

.simple-likes-mobile-show {
    display: inline !important;
}

.simple-likes-mobile-show-block {
    display: block !important;
}

.simple-likes-desktop-hide {
    display: inline !important;
}

.simple-likes-desktop-show {
    display: none !important;
}

.simple-likes-desktop-show-block {
    display: none !important;
}

/* Desktop overrides */
@media (min-width: 640px) {
    .simple-likes-mobile-hide {
        display: inline !important;
    }

    .simple-likes-mobile-hide.min-w-0 {
        display: block !important;
    }

    .simple-likes-mobile-show {
        display: none !important;
    }

    .simple-likes-mobile-show-block {
        display: none !important;
    }

    .simple-likes-desktop-hide {
        display: none !important;
    }

    .simple-likes-desktop-show {
        display: inline !important;
    }

    th.simple-likes-desktop-show,
    td.simple-likes-desktop-show {
        display: table-cell !important;
    }

    .simple-likes-desktop-show-block {
        display: block !important;
    }
}

/* Widget specific styles */
.simple-likes-avatar-container {
    flex-shrink: 0 !important;
}

.simple-likes-avatar-container-active {
    flex-shrink: 0 !important;
    padding-right: 0.5rem !important;
}

.simple-likes-entry-title {
    word-wrap: break-word !important;
    overflow-wrap: break-word !important;
    hyphens: auto !important;
    line-height: 1.4 !important;
    max-height: 2.8em !important; /* 2 lines */
    overflow: hidden !important;
    display: -webkit-box !important;
    -webkit-line-clamp: 2 !important;
    -webkit-box-orient: vertical !important;
}

/* Table column styles */
.simple-likes-col-icon {
    width: 40px !important;
    min-width: 40px !important;
    max-width: 40px !important;
    padding-right: 1.7rem !important;
}

.simple-likes-col-entry {
    padding-left: 0 !important;
    width: 50% !important;
    padding-top: 0.5rem !important;
    padding-bottom: 0.5rem !important;
}

.simple-likes-col-entry-active {
    width: 60% !important;
    padding-top: 0.5rem !important;
    padding-bottom: 0.5rem !important;
}

.simple-likes-col-user {
    padding-left: 0 !important;
    width: 30% !important;
    white-space: nowrap !important;
    min-width: 80px !important;
    text-align: left !important;
    vertical-align: middle !important;
}

.simple-likes-col-user-active {
    width: 4rem !important;
    padding-right: 1rem !important;
}

.simple-likes-col-time {
    padding-left: 0 !important;
    width: 20% !important;
    padding-right: 0.5rem !important;
    text-align: end !important;
    white-space: nowrap !important;
}

/* Popular Entries widget specific columns */
.simple-likes-col-popular-entry {
    padding-left: 0 !important;
    padding-top: 0.5rem !important;
    padding-bottom: 0.5rem !important;
}

.simple-likes-col-collection {
    padding-left: 0 !important;
    white-space: nowrap !important;
}

.simple-likes-col-likes {
    padding-left: 0 !important;
    padding-right: 0.5rem !important;
    text-align: end !important;
    white-space: nowrap !important;
}

/* Mobile column adjustments */
@media (max-width: 639px) {
    .simple-likes-col-icon {
        display: none !important;
    }

    .simple-likes-col-entry {
        width: 75% !important;
        padding-left: 1rem !important;
    }

    .simple-likes-col-user {
        width: 15% !important;
        text-align: right !important;
        min-width: 30px !important;
    }

    .simple-likes-col-time {
        width: 10% !important;
    }

    /* Popular Entries mobile adjustments */
    .simple-likes-col-popular-entry {
        width: 75% !important;
        padding-left: 0.75rem !important;
    }

    .simple-likes-col-likes {
        width: 25% !important;
    }
}
';
    }
}
