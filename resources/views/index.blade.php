<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="SNR">
	<meta name="keywords" content="SNR" >
	<meta name="author" content="http://es.linkedin.com/in/giovanniortegon" >
    <link href="https://servicios.supernotariado.gov.co/vista/img/favicon.ico" rel="icon" type="image/x-icon">
    <title>SNR</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <style>
        body {
            font-size: .875rem;
        }

        .feather {
            width: 16px;
            height: 16px;
            vertical-align: text-bottom;
        }

        /*
        * Sidebar
        */

        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100; /* Behind the navbar */
            padding: 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
        }

        .sidebar-sticky {
            position: -webkit-sticky;
            position: sticky;
            top: 48px; /* Height of navbar */
            height: calc(100vh - 48px);
            padding-top: .5rem;
            overflow-x: hidden;
            overflow-y: auto; /* Scrollable contents if viewport is shorter than content. */
        }

        .sidebar .nav-link {
            font-weight: 500;
            color: #333;
        }

        .sidebar .nav-link .feather {
            margin-right: 4px;
            color: #999;
        }

        .sidebar .nav-link.active {
            color: #007bff;
        }

        .sidebar .nav-link:hover .feather,
        .sidebar .nav-link.active .feather {
            color: inherit;
        }

        .sidebar-heading {
            font-size: .75rem;
            text-transform: uppercase;
        }

        /*
        * Navbar
        */

        .navbar-brand {
            padding-top: .75rem;
            padding-bottom: .75rem;
            font-size: 1rem;
            background-color: rgba(0, 0, 0, .25);
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .25);
        }

        .navbar .form-control {
            padding: .75rem 1rem;
            border-width: 0;
            border-radius: 0;
        }

        .form-control-dark {
            color: #fff;
            background-color: rgba(255, 255, 255, .1);
            border-color: rgba(255, 255, 255, .1);
        }

        .form-control-dark:focus {
            border-color: transparent;
            box-shadow: 0 0 0 3px rgba(255, 255, 255, .25);
        }

        /*
        * Utilities
        */

        .border-top { border-top: 1px solid #e5e5e5; }
        .border-bottom { border-bottom: 1px solid #e5e5e5; }


        .bg-cabezote {
            background:#B40404;
        }

        .bg-menup {
            background:#222D32;
        }

        .bg-menup .nav-link {
            color:#fff;
        }

        .bg-menup img {
            margin-top:-5px;
        }
    </style>
    </head>
    <body>
    @if ($config)
    <nav class="navbar navbar-dark sticky-top bg-cabezote flex-md-nowrap p-0">
        <span class="navbar col-sm-3 col-md-2 mr-0" style="color:#fff;font-weight: bold;font-size:20px;"> SNR</span>
        <div style="color:#fff;padding: 1px 15px 1px 5px;"></div>
    </nav>
    <div class="container-fluid">
      <div class="row" >
        <nav class="col-md-2 d-none d-md-block bg-menup sidebar" >
          <div class="sidebar-sticky">
		  <input type="text" style="background:#374850;color:#ccc;padding: 1px 5px; 1px 5px 1px;width:96%;border-radius:5px;" id="search" value=""  placeholder="Buscar...">
		    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
              <span>Menu</span>
              <a class="d-flex align-items-center text-muted" href="#">
                <span data-feather="plus-circle"></span>
              </a>
            </h6>
            <ul class="nav flex-column">
			  <li class="nav-item">
                <a class="nav-link" href="#" onclick="alert('Notaria');">
                  <img src="images/folder.svg">
                  Notaria
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#">
                  <img src="images/folder.svg">
                  Facturación
                </a>
              </li>
            </ul>
            <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
              <span>Administración</span>
              <a class="d-flex align-items-center text-muted" href="#">
                <span data-feather="plus-circle"></span>
              </a>
            </h6>
            <ul class="nav flex-column mb-2">
              <li class="nav-item">
                <a class="nav-link" href="#">
                  <span data-feather="file-text"></span>
                  Configuración
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">
                  <span data-feather="file-text"></span>
                  Usuarios
                </a>
              </li>
            </ul>
          </div>
        </nav>
        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                <h2>Facturas de SIN </h2>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group mr-2">
                        <input type="date" value="2020-07-07" class="form"><span data-feather="calendar"></span> 
                    </div>
                </div>
            </div>       
            <div class="table-responsive" id="app">
                <table class="table table-striped table-sm" id="mytable">
                    <thead>
                        <tr>
                            <th>Factura</th>
                            <th>Fecha</th>
                            <th>Escritura</th>
                            <th>Cliente</th>
                        </tr>
                    </thead>
                    <tbody>  
                    @foreach ($invoice as $i)
                        <tr>
                            <td>{{ $i->codfactur }}</td>
                            <td>{{ $i->fecfactur }}</td>
                            <td>{{ $i->codradica }}</td>
                            <td>{{ $i->tidusuar }} {{ $i->idusuar }}</td>
                            <td><a href="detalle/" target="_blank"><button type="button" class="btn btn-sm btn-success">Detalles</button></a></td>
                            <td><a href="xml/factura.xml" target="_blank"><button type="button" class="btn btn-sm btn-warning">XML</button></a></td>
                        <tr>
                    @endforeach
                        <tr v-for="user, index in users">
                           
                            <td>
                                <a href="detalle/" target="_blank"><button type="button" class="btn btn-sm btn-success">Detalles</button></a>
                            </td>
                            <td>
                                <a href="xml/factura.xml" target="_blank"><button type="button" class="btn btn-sm btn-warning">XML</button></a>
                            </td>
                        </tr>
                    </tbody>
                </table>		
            </div>
        </main>
      </div>
    </div>
    @else
        No tiene configurado el archivo
    @endif
     <!-- Bootstrap core JavaScript
    ================================================== -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.6.11/vue.min.js"></script>
    <script>
        var app = new Vue({
            el: '#app',
            data: {
                users: []
            },
            mounted: function() {
                axios.get('./sin/')
                    .then(response => {
                        this.users = response.data;
                    })
                    .catch(error => {
                        console.log(error);
                    });
            }
        })
    </script>
    <script>
        
      Holder.addTheme('thumb', {
        bg: '#55595c',
        fg: '#eceeef',
        text: 'Thumbnail'
      });
        // Write on keyup event of keyword input element
        $(document).ready(function(){
            $("#search").keyup(function(){
                _this = this;
                // Show only matching TR, hide rest of them
                $.each($("#mytable tbody tr"), function() {
                    if($(this).text().toLowerCase().indexOf($(_this).val().toLowerCase()) === -1)
                    $(this).hide();
                    else
                    $(this).show();
                });
            });
        });
    </script>
  </body>
</html>
