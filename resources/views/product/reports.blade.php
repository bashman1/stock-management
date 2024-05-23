<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Product Report</title>
</head>
<body>
   
 
    <div class="margin-top">
        <table class="products">
            <tr>
                <th>Name</th>
                <th>Type</th>
                <th>Category</th>
                <th>Measurement</th>
                <th>Purchase price</th>
                <th>Selling Price</th>
                <th>Product Number</th>

            </tr>
            <tr class="items">
                @foreach($products as $item)
                    <td>
                        {{ $item['name'] }}
                    </td>
                    <td>
                        {{ $item['type'] }}
                    </td>
                    <td>
                        {{ $item['category'] }}
                    </td>
                    <td>
                        {{ $item['measurement'] }}
                    </td>
                    <td>
                        {{ $item['purchase_price'] }}
                    </td>
                    <td>
                        {{ $item['selling_price'] }}
                    </td>
                    <td>
                        {{ $item['product_no'] }}
                    </td>
                @endforeach
            </tr>
        </table>
    </div>
 
</body>
</html>