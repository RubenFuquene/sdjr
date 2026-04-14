<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Tu orden ha sido creada!</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f4f5f7;
            margin: 0;
            padding: 0;
            color: #333333;
        }
        .wrapper {
            max-width: 620px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .header {
            background-color: #1a56db;
            padding: 32px 40px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            font-size: 22px;
            margin: 0;
            font-weight: 600;
        }
        .content {
            padding: 36px 40px;
        }
        .greeting {
            font-size: 16px;
            color: #111827;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .text {
            font-size: 15px;
            color: #4b5563;
            line-height: 1.6;
            margin-bottom: 12px;
        }
        .section-title {
            font-size: 14px;
            font-weight: 700;
            color: #111827;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin: 28px 0 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
            font-size: 14px;
        }
        thead tr {
            background-color: #f3f4f6;
        }
        th {
            border: 1px solid #e5e7eb;
            padding: 10px 14px;
            text-align: left;
            color: #374151;
            font-weight: 600;
        }
        th.center { text-align: center; }
        th.right  { text-align: right; }
        td {
            border: 1px solid #e5e7eb;
            padding: 10px 14px;
            color: #4b5563;
        }
        td.center { text-align: center; }
        td.right  { text-align: right; }
        tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .summary-box {
            background-color: #f0f4ff;
            border-left: 4px solid #1a56db;
            border-radius: 4px;
            padding: 16px 20px;
            margin-bottom: 16px;
        }
        .summary-box p {
            margin: 4px 0;
            font-size: 14px;
            color: #374151;
        }
        .summary-box .total {
            font-size: 16px;
            font-weight: 700;
            color: #111827;
            margin-top: 8px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 13px;
            font-weight: 600;
            background-color: #dbeafe;
            color: #1e40af;
        }
        .footer {
            background-color: #f9fafb;
            border-top: 1px solid #e5e7eb;
            padding: 24px 40px;
            text-align: center;
            font-size: 13px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Header -->
        <div class="header">
            <h1>¡Tu orden ha sido creada!</h1>
        </div>

        <!-- Body -->
        <div class="content">
            <p class="greeting">Hola {{ $notifiable->name }},</p>
            <p class="text">Tu orden <strong>#{{ $order->id }}</strong> ha sido creada exitosamente.</p>
            <p class="text">A continuación encontrarás los detalles de tu pedido:</p>

            <!-- Products Table -->
            <p class="section-title">Productos</p>
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th class="center">Cantidad</th>
                        <th class="right">Precio Unit.</th>
                        <th class="right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $item)
                        <tr>
                            <td>{{ $item->product->title }}</td>
                            <td class="center">{{ $item->quantity }}</td>
                            <td class="right">${{ number_format($item->unit_price, 2) }}</td>
                            <td class="right">${{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Summary -->
            <div class="summary-box">
                @if ($branchInfo)
                    <p><strong>Sucursal:</strong> {{ $branchInfo }}</p>
                @endif
                <p><strong>Estado:</strong> <span class="status-badge">{{ $order->status }}</span></p>
                <p class="total">Total: ${{ number_format($order->total_price, 2) }}</p>
            </div>

            <p class="text">¡Gracias por tu compra!</p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Este correo fue enviado automáticamente. Por favor no respondas a este mensaje.</p>
        </div>
    </div>
</body>
</html>
