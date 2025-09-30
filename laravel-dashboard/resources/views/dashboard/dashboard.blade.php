@extends('templates/template')
@section('content')
<style>
    .auto-size-btn {
        height: 40px;
        line-height: 40px;
        padding: 0 15px;
        font-size: 14px;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
    body {
      font-family: 'Poppins', sans-serif;
      margin: 0;
      padding: 0;
      background-color: #f4f4f4;
      color: #333;
      -webkit-font-smoothing: antialiased;
    }
    p {
      line-height: 1.6;
      padding: 0 20px;
    }
    h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6 {
      font-weight: 700;
      line-height: 1.2;
      letter-spacing: 0.8px;
    }
    .card {
      box-shadow: none;
    }
    .gender-number {
        font-size: 25px;
        font-weight: 700;
        color: #495057;
        margin-bottom: 0px;
    }
    .gender-label {
        font-weight: 700;
        font-size: 22px; /* Memperbesar ukuran font label */
        color: #495057;
        margin-bottom: 0px; /* Menambahkan margin bawah untuk jarak dengan penjelasan */
    }
    .gender-description {
        font-size: 14px;
        color: #6c757d;
        margin-bottom: 10px;
        padding: 0px;
    }
    .gender-total {
        color:#ae9f9f;
    }
    .fw-700 {
        font-weight: 700;
    }
    .custom-header {
        text-align: center;
        position: relative;
    }
    .custom-header::before, .custom-header::after {
        content: '';
        position: absolute;
        top: 50%;
        width: 40%;
        height: 2px;
        background-color: #000;
    }
    .custom-header::before {
        left: 0;
    }
    .custom-header::after {
        right: 0;
    }
</style>
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-4">
                    <h1>Dashboard</h1>
                </div>
            </div>
        </div>
        <!-- /.container-fluid -->
    </section>
    <!-- Main content -->
    <section class="content">
        <div id="main"></div>
        <div class="container-fluid">

        </div>
        <!-- /.container-fluid -->
    </section>
    <!-- /.content -->
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.3.3/dist/echarts.min.js"></script>

    <script>


    </script>
@endsection
