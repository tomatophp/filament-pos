<?php

return [
    "title" => "نقاط البيع",
    "group" => "الطلبات",
    "widgets" => [
        "count" => "إجمالي عدد الطلبات اليوم",
        "money" => "إجمالي قيمة الطلبات اليوم"
    ],
    "table" => [
        "search" => 'البحث بإسم المنتج أو الباركود',
        "columns" => [
            "image" => "صورة",
            "name" => "الإسم",
            "sku" => "كود المنتج",
            "barcode" => "الباركود",
            "price" => "السعر",
        ],
        "actions" => [
            "addToCart" => "إضافة إلى السلة"
        ],
        "filters" => [
            "category_id" => "التصنيف"
        ]
    ],
    "notifications" => [
        "delete" => [
            "title" => "نجاح",
            "message" => "تمت إزالة المنتج من السلة بنجاح"
        ],
        "clear" => [
            "title" => "نجاح",
            "message" => "تم تفريغ السلة بنجاح"
        ],
        "checkout" => [
            "title" => "نجاح",
            "message" => 'تم إنشاء الطلب رقم #:uuid بنجاح',
            "print" => "طباعة الفاتورة",
            "view" => "عرض الطلب"
        ]
    ],
    "actions" => [
        "checkout" => [
            "label" => "الدفع",
            "form" => [
                "account_id" => "الحساب",
                "payment_method" => "طريقة الدفع",
                "paid_amount" => "المبلغ المدفوع",
                "coupon" => "الكوبون",
            ],
            "account" => [
                "name" => "الإسم",
                "email" => "البريد الإلكتروني",
                "phone" => "الهاتف",
                "address" => "العنوان",
            ]
        ]
    ],
    "view" => [
        "cart" => "السلة",
        "totals" => "الإجماليات",
        "empty" => "لا يوجد منتجات في السلة",
        "clear" => "تفريغ السلة",
        "remove" => "إزالة من السلة",
        "subtotal" => "الإجمالي",
        "discount" => "الخصم",
        "vat" => "الضريبة",
        "total" => "الإجمالي",
    ]
];
