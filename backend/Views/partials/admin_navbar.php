<?php
/** Admin navigation — include in all admin views */
?>
<div class="navbar site-navbar">
    <div class="navbar-inner">
        <a href="/ethiotrip1/ethiotrip/public/admin/dashboard" class="logo">Ethio<span>Trip</span> Admin</a>
        <button type="button" class="nav-toggle" aria-label="Open menu" aria-expanded="false" aria-controls="adminMainNav">
            <span class="nav-toggle-bar"></span>
            <span class="nav-toggle-bar"></span>
            <span class="nav-toggle-bar"></span>
        </button>
    </div>
    <div class="nav site-nav-panel" id="adminMainNav">
        <a href="/ethiotrip1/ethiotrip/public/admin/dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="/ethiotrip1/ethiotrip/public/admin/bookings"><i class="fas fa-credit-card"></i> Payments</a>
        <a href="/ethiotrip1/ethiotrip/public/admin/packages"><i class="fas fa-box"></i> Packages</a>
        <a href="/ethiotrip1/ethiotrip/public/admin/destinations"><i class="fas fa-map-marker-alt"></i> Destinations</a>
        <a href="/ethiotrip1/ethiotrip/public/admin/discounts"><i class="fas fa-percent"></i> Discounts</a>
        <a href="/ethiotrip1/ethiotrip/public/"><i class="fas fa-home"></i> Home</a>
        <a href="/ethiotrip1/ethiotrip/public/logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>
