<!doctype html>
<html>
<head>
  <title>xo-planet</title>
  <link rel="shortcut icon" href="/favicon.ico" />
  <link href='http://fonts.googleapis.com/css?family=Josefin+Sans' rel='stylesheet' type='text/css'>
</head>

<body id="posts-show">
  <style>
    body {
      font-family: 'Josefin Sans', sans-serif;
    }
    svg {
      vertical-align: middle;
      background: rgba(255,255,255, 0.2);
      box-shadow: inset 0 0 3px 0px #CECECE;
    }

    svg circle {
      stroke-width: 2px;
      stroke: #79A32B;
      fill: transparent;
      cursor: pointer;
    }

    svg circle:active {
      stroke: #45D3C7;
    }
  </style>

  <?php echo '<p>Hello World</p>'; ?>

  <br>
  <br>
  <div id="title" style="font-size:20px; text-align:center;">x o planet</div>
  <br><br>

  <div id="collision" style="padding:20px; text-align: center;"></div><br><br>

  <div id="demoAddRandomAndResize" style="text-align: center;">

    <div style="display:block;padding:20px">
      <button class="add-data action-button">Add data</button>
    </div>
  </div><br><br>



  <script src="http://cdnjs.cloudflare.com/ajax/libs/d3/3.3.3/d3.min.js" type="text/javascript"></script>
  <script>

    // Sample data.
    /*
      {
        "name":"HD 209458 b",
        "mass":0.714,
        "radius":1.38,
        "discovered":1999,
        "water":"1",
        "star_name":"HD 209458",
        "star_distance":47,
        "star_mass":1.148,
        "star_radius":1.146,
        "star_age":4
      }
    */

    var format = d3.time.format("%a %b %d %Y")
    var amountFn = function(d) { return d.amount }
    var dateFn = function(d) { return format.parse(d.created_at) }
    var planetsPerYear = {0:0};
    var countPerYear = function(data) {
      for (var i = 0; i < data.length; i++){
        if (data[i].discovered in planetsPerYear) {
          planetsPerYear[data[i].discovered]++;
        } else if (data[i].discovered) {
          planetsPerYear[data[i].discovered] = 1;
        } else {
          // Planets that don't have a year of discovery.
          planetsPerYear[0]++;
        }
      }
    }

    // Get data
    var exoplanets;
    var xhr = new XMLHttpRequest();
    xhr.open('GET','exoplanet.json',true);

    xhr.onload = function() {
      if (this.readyState != 4) {
        return;
      }
      exoplanets = JSON.parse(xhr.responseText);
      console.log(exoplanets[0]);
      countPerYear(exoplanets);
      console.log(planetsPerYear);
      JSONData = exoplanets;
      ready();
    }
    xhr.send(null);



    var planetsToNodes = function(){
      var planets = [];
      for (var i = 0; i < 10; i++){//exoplanets.length; i++){
        planets.push({radius: exoplanets[i].radius*5});
      }
      return planets;
    }


    var ready = function() {


    // BOX ONE.

      var width = 500,
          height = 500,
          color = d3.scale.category10();

      var nodes = d3.range(10).map(function(i) { return {radius: exoplanets[i].radius*20 };});

      //d3.range(10).map(function() { return {radius: Math.random() * 12 + 20}; });
      console.log(nodes);
      console.log(planetsToNodes());

      var root = nodes[0];
      root.x = width/2;
      root.y = height/2;
      root.fixed = true;
      var colorMap = {};
      for (var i = 1; i < nodes.length; i++) {
        colorMap[i] = color(Math.random() * 10);
      }


      var force = d3.layout.force()
          .gravity(0.01)
          .charge(function(d, i) { return i ? 20 : -200; })
          .nodes(nodes)
          .size([width, height]);

      force.start();

      var canvas = d3.select("#collision").append("canvas")
          .attr("width", width)
          .attr("height", height)
          .attr("style", "border:1px solid");

      var context = canvas.node().getContext("2d");

      force.on("tick", function(e) {
        var q = d3.geom.quadtree(nodes),
            i,
            d,
            n = nodes.length;

        for (i = 0; i < n; ++i) q.visit(collide(nodes[i]));
        context.clearRect(0, 0, width, height);

        for (i = 0; i < n; ++i) {
          context.beginPath();
          d = nodes[i];
          context.moveTo(d.x, d.y);
          context.fillStyle = colorMap[i];
          context.arc(d.x, d.y, d.radius, 0, 2 * Math.PI);
          context.fill();
        }
        context.beginPath();
        context.fillStyle = colorMap[i];
        context.font = '10pt Calibri';
        context.fillText("Earth", width/2+10, height/2+10);
        context.fill();


      });

      canvas.on("mousemove", function() {
        var p1 = d3.mouse(this);

        force.resume();
      });

      function collide(node) {
        var r = node.radius + 40,
            nx1 = node.x - r,
            nx2 = node.x + r,
            ny1 = node.y - r,
            ny2 = node.y + r;
        return function(quad, x1, y1, x2, y2) {
          if (quad.point && (quad.point !== node)) {
            var x = node.x - quad.point.x,
                y = node.y - quad.point.y,
                l = Math.sqrt(x * x + y * y),
                r = node.radius + quad.point.radius;
            if (l < r) {
              l = (l - r) / l * .5;
              node.x -= x *= l;
              node.y -= y *= l;
              quad.point.x += x;
              quad.point.y += y;
            }
          }
          return x1 > nx2 || x2 < nx1 || y1 > ny2 || y2 < ny1;
        };
      }














      // BOX TWO


      var data = JSONData.slice()
      var x = d3.time.scale()
        .range([10, 280])
        .domain(d3.extent(data, dateFn))

      var y = d3.scale.linear()
       .range([180, 10])
       .domain(d3.extent(data, amountFn))

      var svg = d3.select("#demoAddRandomAndResize").append("svg:svg")
       .attr("width", 300)
       .attr("height", 200)

      var start = d3.min(data, dateFn)
      var end = d3.max(data, dateFn)

      var refreshGraph = function() {

      x.domain(d3.extent(data, dateFn))
      y.domain(d3.extent(data, amountFn))

      var circles = svg.selectAll("circle").data(data)

      circles.transition()
       .attr("cx", function(d) { return x(dateFn(d)) })
       .attr("cy", function(d) { return y(amountFn(d)) })

      circles.enter()
       .append("svg:circle")
       .attr("r", 4)
       .attr("cx", function(d) { return x(dateFn(d)) })
       .attr("cy", function(d) { return y(amountFn(d)) })
       .on("click", function(d) {
          d3.select("#demoAddRandomAndResize .value").text("Date: " + d.created_at + " amount: " + d.amount)
       })

      }

      d3.selectAll("#demoAddRandomAndResize .add-data")
        .on("click", function() {
          var date = new Date(end.getTime() + Math.random() * (end.getTime() - start.getTime()))
          obj = {
            'id': Math.floor(Math.random()*70),
            'amount': Math.floor(1000 + Math.random()*20001),
            'created_at': date.toDateString()
          }
          data.push(obj)
          refreshGraph()
        })

      refreshGraph()


    };


  </script>

</body>
</html>

