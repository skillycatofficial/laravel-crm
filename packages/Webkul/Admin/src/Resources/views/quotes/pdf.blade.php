<!DOCTYPE html>
<html lang="{{ $locale = app()->getLocale() }}" dir="{{ in_array($locale, ['fa', 'ar']) ? 'rtl' : 'ltr' }}">
<head>
    <meta http-equiv="Cache-control" content="no-cache">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    @php
        if ($locale == 'en') {
            $fontFamily = ['regular' => 'DejaVu Sans', 'bold' => 'DejaVu Sans'];
        } else {
            $fontFamily = ['regular' => 'Arial, sans-serif', 'bold' => 'Arial, sans-serif'];
        }
        if (in_array($locale, ['ar', 'fa', 'tr'])) {
            $fontFamily = ['regular' => 'DejaVu Sans', 'bold' => 'DejaVu Sans'];
        }
    @endphp

    <style type="text/css">
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: {{ $fontFamily['regular'] }};
        }

        body {
            font-size: 11px;
            color: #333333;
            font-family: "{{ $fontFamily['regular'] }}";
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        /* Header Section */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }

        .header-left {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .header-right {
            display: table-cell;
            width: 50%;
            text-align: right;
            vertical-align: top;
        }

        .logo-section {
            margin-bottom: 10px;
        }

        .logo {
            width: 50px;
            height: 50px;
            background-color: #1F2937;
            display: inline-block;
            vertical-align: middle;
            margin-right: 10px;
            text-align: center;
            line-height: 50px;
            color: white;
            font-size: 16px;
            font-weight: bold;
        }

        .company-name {
            display: inline-block;
            vertical-align: middle;
            font-size: 24px;
            font-weight: bold;
            color: #1F2937;
        }

        .company-details {
            margin-top: 5px;
            font-size: 10px;
            color: #6B7280;
            line-height: 1.6;
        }

        .quote-number {
            background-color: #F3F4F6;
            padding: 8px 15px;
            display: inline-block;
            margin-bottom: 15px;
            font-size: 11px;
            color: #6B7280;
        }

        .total-amount-box {
            margin-top: 10px;
        }

        .total-amount-label {
            font-size: 11px;
            color: #6B7280;
            margin-bottom: 5px;
        }

        .total-amount-value {
            font-size: 28px;
            font-weight: bold;
            color: #1F2937;
        }

        /* Info Section */
        .info-section {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }

        .info-left {
            display: table-cell;
            width: 40%;
            vertical-align: top;
        }

        .info-right {
            display: table-cell;
            width: 60%;
            vertical-align: top;
            padding-left: 20px;
        }

        .info-item {
            margin-bottom: 15px;
        }

        .info-label {
            font-size: 10px;
            color: #6B7280;
            margin-bottom: 3px;
            text-transform: uppercase;
        }

        .info-value {
            font-size: 11px;
            color: #1F2937;
            line-height: 1.5;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .items-table thead {
            background-color: #374151;
            color: white;
        }

        .items-table thead th {
            padding: 12px 10px;
            text-align: left;
            font-size: 10px;
            font-weight: normal;
            text-transform: uppercase;
        }

        .items-table tbody td {
            padding: 12px 10px;
            border-bottom: 1px solid #E5E7EB;
            font-size: 11px;
            color: #374151;
        }

        .items-table tfoot td {
            padding: 8px 10px;
            font-size: 11px;
        }

        .text-right {
            text-align: right !important;
        }

        .text-center {
            text-align: center !important;
        }

        .total-row {
            background-color: #F9FAFB;
        }

        .grand-total-row {
            background-color: #1F2937;
            color: white;
            font-weight: bold;
            font-size: 12px;
        }

        /* Terms Section */
        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #1F2937;
            margin-bottom: 10px;
        }

        .terms-section {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #E5E7EB;
        }

        .terms-content {
            font-size: 10px;
            color: #6B7280;
            line-height: 1.6;
        }

        /* Summary Table */
        .summary-table {
            width: 350px;
            float: right;
            margin-top: 20px;
        }

        .summary-table td {
            padding: 8px 10px;
            font-size: 11px;
        }

        .summary-table td:first-child {
            color: #6B7280;
        }

        .summary-table td:last-child {
            text-align: right;
            color: #1F2937;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <div class="logo-section">
                    <!-- Company Logo (you can add an image here) -->
                    <div class="logo">
                        {{ strtoupper(substr(config('app.name', 'CRM'), 0, 3)) }}
                    </div>
                    <div class="company-name">
                        {{ config('app.name', 'SkillyCRM') }}
                    </div>
                </div>
                <div class="company-details">
                    {{ core()->getConfigData('sales.settings.company.vat_id') ?: 'www.yourcompany.com' }}<br>
                    {{ core()->getConfigData('sales.settings.company.contact_email') ?: 'info@yourcompany.com' }}<br>
                    {{ core()->getConfigData('sales.settings.company.contact_number') ?: '+91 1234567890' }}
                </div>
            </div>
            
            <div class="header-right">
                <div class="quote-number">
                    QUOTE #{{ $quote->id }}
                </div>
                <div class="total-amount-box">
                    <div class="total-amount-label">Total Amount</div>
                    <div class="total-amount-value">
                        {!! core()->formatBasePrice($quote->grand_total, true) !!}
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Section -->
        <div class="info-section">
            <div class="info-left">
                <div class="info-item">
                    <div class="info-label">Quote Date</div>
                    <div class="info-value">{{ core()->formatDate($quote->created_at, 'd/m/Y') }}</div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Expired At</div>
                    <div class="info-value">{{ core()->formatDate($quote->expired_at, 'd/m/Y') }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">Sales Person</div>
                    <div class="info-value">{{ $quote->user->name }}</div>
                </div>
            </div>
            
            <div class="info-right">
                <div class="info-item">
                    <div class="info-label">Billing Address</div>
                    <div class="info-value">
                        <strong>{{ $quote->person->name }}</strong><br>
                        @if ($quote->person->job_title)
                            {{ $quote->person->job_title }}<br>
                        @endif
                        @if ($quote->person->organization)
                            {{ $quote->person->organization->name }}<br>
                        @endif
                        @if ($quote->billing_address)
                            {{ $quote->billing_address['address'] ?? '' }}<br>
                            {{ $quote->billing_address['city'] ?? '' }} {{ $quote->billing_address['postcode'] ?? '' }}<br>
                            {{ $quote->billing_address['state'] ?? '' }}<br>
                            {{ core()->country_name($quote->billing_address['country'] ?? '') }}
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Description Section -->
        @if ($quote->subject || $quote->description)
        <div class="info-item" style="margin-bottom: 20px;">
            <div class="info-label">Description</div>
            <div class="info-value">
                {{ $quote->subject }}
                @if ($quote->description)
                    <br>{{ $quote->description }}
                @endif
            </div>
        </div>
        @endif

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 10%;">SKU</th>
                    <th style="width: 22%;">PRODUCT NAME</th>
                    <th style="width: 10%;" class="text-right">PRICE</th>
                    <th style="width: 8%;" class="text-center">QUANTITY</th>
                    <th style="width: 12%;" class="text-right">AMOUNT</th>
                    <th style="width: 12%;" class="text-right">DISCOUNT</th>
                    <th style="width: 12%;" class="text-right">TAX</th>
                    <th style="width: 14%;" class="text-right">GRAND TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($quote->items as $item)
                <tr>
                    <td>{{ $item->sku }}</td>
                    <td>{{ $item->name }}</td>
                    <td class="text-right">{!! core()->formatBasePrice($item->price, true) !!}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">{!! core()->formatBasePrice($item->total, true) !!}</td>
                    <td class="text-right">{!! core()->formatBasePrice($item->discount_amount, true) !!}</td>
                    <td class="text-right">{!! core()->formatBasePrice($item->tax_amount, true) !!}</td>
                    <td class="text-right">{!! core()->formatBasePrice($item->total + $item->tax_amount - $item->discount_amount, true) !!}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Summary -->
        <div class="summary-table">
            <table style="width: 100%;">
                <tr>
                    <td>Sub Total</td>
                    <td class="text-right">{!! core()->formatBasePrice($quote->sub_total, true) !!}</td>
                </tr>
                @if ($quote->tax_amount > 0)
                <tr>
                    <td>Tax</td>
                    <td class="text-right">{!! core()->formatBasePrice($quote->tax_amount, true) !!}</td>
                </tr>
                @endif
                @if ($quote->discount_amount > 0)
                <tr>
                    <td>Discount</td>
                    <td class="text-right">-{!! core()->formatBasePrice($quote->discount_amount, true) !!}</td>
                </tr>
                @endif
                @if ($quote->adjustment_amount != 0)
                <tr>
                    <td>Adjustment</td>
                    <td class="text-right">{!! core()->formatBasePrice($quote->adjustment_amount, true) !!}</td>
                </tr>
                @endif
                <tr class="grand-total-row">
                    <td style="color: white;">Total Price</td>
                    <td class="text-right" style="color: white;">{!! core()->formatBasePrice($quote->grand_total, true) !!}</td>
                </tr>
            </table>
        </div>

        <div style="clear: both;"></div>

        <!-- Terms & Conditions -->
        <div class="terms-section">
            <div class="section-title">Terms & Conditions</div>
            <div class="terms-content">
                {{ core()->getConfigData('sales.settings.company.payment_terms') ?: 'Please pay within 15 days of receiving this quote.' }}
                @if ($quote->description)
                    <br><br>{{ $quote->description }}
                @endif
            </div>
        </div>
    </div>
</body>
</html>
