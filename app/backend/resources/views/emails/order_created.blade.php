<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tu orden ha sido creada</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; background: #f4f5f7; color: #1f2937; margin: 0; padding: 24px; }
        .wrapper { max-width: 620px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; border: 1px solid #e5e7eb; }
        .header { text-align: center; padding: 24px; border-bottom: 1px solid #e5e7eb; }
        .header img { max-width: 180px; height: auto; }
        .content { padding: 24px; }
        table { width: 100%; border-collapse: collapse; margin: 12px 0 20px; }
        th, td { border: 1px solid #e5e7eb; padding: 8px; font-size: 13px; }
        th { background: #f9fafb; text-align: left; }
        .right { text-align: right; }
        .center { text-align: center; }
        .footer { padding: 16px 24px; border-top: 1px solid #e5e7eb; background: #f9fafb; font-size: 12px; color: #6b7280; text-align: center; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <img src="{{ asset('brand/isotipo-512-napa.png') }}" alt="{{ config('mail.from.name', config('app.name')) }}">
        </div>

        <div class="content">
            <p><strong>Hola {{ $notifiable->name }},</strong></p>
            <p>Tu orden <strong>#{{ $order->id }}</strong> ha sido creada exitosamente.</p>
            <p>A continuación encontrarás los detalles de tu pedido:</p>

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

            @if ($branchInfo)
                <p><strong>Sucursal:</strong> {{ $branchInfo }}</p>
            @endif
            <p><strong>Estado:</strong> {{ $order->status }}</p>
            <p><strong>Total:</strong> ${{ number_format($order->total_price, 2) }}</p>

            <p>Gracias por tu compra.</p>
            <p>Saludos,<br>{{ config('mail.from.name', config('app.name')) }}</p>
        </div>

        <div class="footer">
            © {{ date('Y') }} {{ config('mail.from.name', config('app.name')) }}. All rights reserved.
        </div>
    </div>
</body>
</html>
