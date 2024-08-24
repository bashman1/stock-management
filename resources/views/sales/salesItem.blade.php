<!doctype html>
<html lang="en">

<head>

    <style>
        @page {
            margin: 100px 25px; /* Set margin to leave space for the header and footer */
        }

        body {
            margin: 0;
            padding: 0;
        }

        /* Header styles */
        header {
            position: fixed;
            top: -80px;
            left: 0px;
            right: 0px;
            height: 50px;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            line-height: 35px;
        }

        /* Footer styles */
        footer {
            position: fixed;
            bottom: -40px;
            left: 0px;
            right: 0px;
            height: 30px;
            text-align: center;
            font-size: 12px;
            line-height: 20px;
        }

        /* Main content styles */
        .content {
            text-align: left;
            font-size: 14px;
        }
    </style>

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css"
          integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">

    <title>Sale Report</title>
</head>

<body>

    <!-- Header content -->
{{-- <header>
    Sales Report - {{ now()->format('Y-m-d') }}
</header> --}}

<!-- Footer content -->
{{-- <footer>
    Page: {PAGE_NUM}/{PAGE_COUNT}
</footer> --}}


<div class="margin-top">
    <table class="table">
        <thead>
        <tr>
            <th scope="col">Date</th>
            <th scope="col">Name</th>
            <th scope="col">Quantity Sold</th>
            <th scope="col">Measurement Unit</th>
            <th scope="col">Amount</th>
            <th scope="col">Selling Price</th>
            <th scope="col">Profit Margin</th>
        </tr>
        </thead>
        <tbody>
        @foreach($sales as $item)
        <tr class="items">

            <td scope="row">
                {{ $item->created_at }}
            </td>
            <td>
                {{$item->product_name }}
            </td>
            <td>
                {{$item->quantity }}
            </td>
            <td>
                {{$item->measurement_unit }}
            </td>
            <td>
                {{$item->quantity * $item->selling_price }}
            </td>
            <td>
                {{$item->selling_price }}
            </td>
            <td>
                {{$item->selling_price - $item->purchase_price }}
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
