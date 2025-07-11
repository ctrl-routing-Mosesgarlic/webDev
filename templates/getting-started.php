<?php
require_once '../connection.php';
require_once '../session.php';

// Start session
$session = new Session();
$session->start();

// Database connection
$db = new database();
$conn = $db->getConnection();

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
?>

{% extends "base.html" %}

{% block head_extra %}
    {{ parent() }}
    <link rel="stylesheet" href="/static/css/get-started.css">
{% endblock %}

{% block main %}
<section class="getting-started container">
    <h1>Getting Started Guide</h1>
    
    {% if isLoggedIn %}
    <div class="welcome-message">
        <p>Welcome back! Here's how to get the most from our system.</p>
    </div>
    {% endif %}
    
    <div class="guide-steps">
        <div class="step">
            <h2>1. Account Setup</h2>
            <p>{% if isLoggedIn %}You've completed this step!{% else %}Create your account and set up your profile.{% endif %}</p>
        </div>
        
        <div class="step">
            <h2>2. Browse Products</h2>
            <p>Explore our warehouse inventory and categories.</p>
            {% if isLoggedIn %}
            <a href="/templates/categories/" class="btn btn-primary">View Categories</a>
            {% endif %}
        </div>
        
        <div class="step">
            <h2>3. Make Your First Order</h2>
            <p>{% if isLoggedIn %}Ready to place an order?{% else %}Add products to your cart and complete checkout.{% endif %}</p>
            {% if isLoggedIn %}
            <a href="/cart" class="btn btn-primary">Go to Cart</a>
            {% endif %}
        </div>
    </div>
</section>
{% endblock %}
