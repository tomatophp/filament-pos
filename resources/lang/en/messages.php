<?php

return [
    "title" => "POS",
    "group" => "Ordering",
    "widgets" => [
        "count" => "Total Orders Today",
        "money" => "Total Order Money Today"
    ],
    "table" => [
        "search" => 'Search By Product Name or Barcode Scan',
        "columns" => [
            "image" => "Image",
            "name" => "Name",
            "sku" => "SKU",
            "barcode" => "Barcode",
            "price" => "Price",
        ],
        "actions" => [
            "addToCart" => "Add To Cart"
        ],
        "filters" => [
            "category_id" => "Category"
        ]
    ],
    "notifications" => [
        "delete" => [
            "title" => "Success",
            "message" => "Product Removed From Cart Successfully"
        ],
        "clear" => [
            "title" => "Success",
            "message" => "Cart Cleared Successfully"
        ],
        "checkout" => [
            "title" => "Success",
            "message" => 'Order #:uuid Has Been Created',
            "print" => "Print Receipt",
            "view" => "View Order"
        ]
    ],
    "actions" => [
        "checkout" => [
            "label" => "Checkout",
            "form" => [
                "account_id" => "Account",
                "payment_method" => "Payment Method",
                "paid_amount" => "Paid Amount",
                "coupon" => "Coupon",
            ],
            "account" => [
                "name" => "Name",
                "email" => "Email",
                "phone" => "Phone",
                "address" => "Address",
            ]
        ]
    ],
    "view" => [
        "cart" => "Cart",
        "totals" => "Totals",
        "empty" => "No items in cart",
        "clear" => "Clear Cart",
        "remove" => "Remove From Cart",
        "subtotal" => "Subtotal",
        "discount" => "Discount",
        "vat" => "VAT",
        "total" => "Total",
    ]
];
