<!DOCTYPE html>
<html lang="id">
	<head>
	<meta charset="utf-8" />
	<title>Aplikasi Kasir - Kopi Senja POS</title>
	<link rel="icon" type="image/x-icon" href="/img/favicon.ico" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<link href="/kasir/templates/bootstrap.min.css" rel="stylesheet" />
	<style>
        body { background-color: #f8f9fa; }
        .card-product { transition: 0.3s; border: 1px solid #dee2e6; height: 100%; }
        .card-product:hover { transform: translateY(-3px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); cursor: pointer; border-color: #dcb36d; }
        .total-section { background-color: #fff; border-top: 2px solid #eee; padding: 15px; }
        .navbar-brand { font-weight: bold; color: #4e342e !important; }
        .btn-kopi { background-color: #4e342e; color: white; border-color: #4e342e; }
        .btn-kopi:hover { background-color: #3b2723; color: white; border-color: #3b2723; }
        /* Style tambahan untuk tampilan POS */
        .pos-container { height: calc(100vh - 56px); } /* Mengisi sisa layar setelah navbar */
        .product-list { overflow-y: auto; max-height: calc(100vh - 120px); } /* Scrollable product area */
        .order-summary { background-color: #ffffff; border-left: 1px solid #dee2e6; }
    </style>
	</head>
	<body>