<!doctype html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css"
        integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">

    <title>Product Report</title>
</head>

<body>


    <div class="margin-top">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Name</th>
                    <th scope="col">Type</th>
                    <th scope="col">Category</th>
                    <th scope="col">Measurement</th>
                    <th scope="col">Purchase price</th>
                    <th scope="col">Selling Price</th>
                    <th scope="col">Product Number</th>

                </tr>
            </thead>
            <tbody>
                @foreach($products as $item)
                    <tr class="items">

                        <td scope="row">
                            {{ $item->name }}
                        </td>
                        <td>
                            {{isset($item->product_type) ? $item->product_type['name'] : 'N/F'}}
                        </td>
                        <td>

                            {{isset($item->category) ? $item->category['name'] : 'N/F'}}
                        </td>
                        <td>

                            {{isset($item->measurement) ? $item->measurement['name'] : 'N/F'}}

                        </td>
                        <td>
                            {{isset($item->stock) ? $item->stock['purchase_price'] : 'N/F'}}
                        </td>
                        <td>
                            {{isset($item->stock) ? $item->stock['selling_price'] : 'N/F'}}
                        </td>
                        <td>
                            {{$item->product_no}}
                        </td>

                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
        integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js"
        integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49"
        crossorigin="anonymous"></script>
</body>

</html>