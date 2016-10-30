<?php

use \yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $items */

$this->title = Yii::t('rbac-admin', 'RBAC-GUI');
$this->params['breadcrumbs'][] = $this->title;
list(,$url) = Yii::$app->assetManager->publish('@mdm/admin/assets');
$this->registerCssFile($url . '/gui.css');

?>
<h1><?= Html::encode($this->title) ?></h1>
<div id="gui-container" class="col-xs-12">
</div>
<script src="//d3js.org/d3.v4.js"></script>
<script language="JavaScript">
    var container = d3.select("#gui-container"),
        width = container.node().getBoundingClientRect().width,
        height = container.node().getBoundingClientRect().height,
        r = 8;  // node radius
    var svg = container
        .append('svg')
        .attr('width', width)
        .attr('height', height);

    d3.json('<?= \yii\helpers\Url::toRoute('rbac-items') ?>', function (error, rbacItems) {
        if (error) throw error;

        var simulation = d3.forceSimulation()
            .nodes(rbacItems.nodes)
            .force("charge", d3.forceManyBody()
                .strength(-350)
                /*.distanceMax(100)*/)
            .force('center', d3.forceCenter(width / 2, height / 2))
            .force("link", d3.forceLink(rbacItems.links)
                .id(function(d) { return d.id; })
                .distance(150));

        // // define arrow markers for graph links
        svg.append('svg:defs').append('svg:marker')
            .attr('id', 'markerArrow')
            .attr('viewBox', '0 -5 10 10')
            .attr('refX', 20)
            .attr('markerWidth', 6)
            .attr('markerHeight', 6)
            .attr('orient', 'auto')
            .append('path')
            .attr('d', 'M0,-5L10,0L0,5 L10,0 L0, -5')
            .style('stroke', '#aaa');

        // create links
        var link = svg.selectAll('.guiLink')
            .data(rbacItems.links)
            .enter().append('line')
            .attr('class', 'guiLink')
            .style('marker-end', 'url(#markerArrow)');

        // create nodes
        var node = svg.selectAll('.guiNode')
            .data(rbacItems.nodes, function (d) { return d.id; })
            .enter().append('g')
            .attr('class', 'guiNode')
            .call(d3.drag()
                .on('start', dragstarted)
                .on('drag', dragged)
                .on('end', dragended))
            .call(d3.zoom());

        // node
        node.append('circle')
            .attr('r', function (d) {
                return d.rule > '' ? r + 3 : 0
            })
            .classed('guiNodeRule', function (d) {
                return d.rule > '';
            });
        node.append('circle')
            .attr('r', r)
            .classed('guiNodeRole', function (d) {
                return d.type == 1;
            })
            .classed('guiNodePermission', function (d) {
                return d.type == 2;
            });
        // node title
        node.append('text')
            .attr('dx', 13)
            .attr('dy', '.35em')
            .text(function (d) {
                return d.id
            });
        // rule title
        node.append('text')
            .attr('dx', 10)
            .attr('dy', '1.35em')
            .classed('guiRuleName', true)
            .text(function (d) {
                return d.rule
            });


        simulation.on('tick', function () {
            node.attr('transform', function (d) {
                return 'translate('
                    + Math.max(r, Math.min(width - r, d.x))
                    + ','
                    + Math.max(r, Math.min(height - r, d.y))
                    + ')';
            });
            link
                .attr('x1', function (d) { return Math.max(r, Math.min(width - r, d.source.x)); })
                .attr('y1', function (d) { return Math.max(r, Math.min(height - r, d.source.y)); })
                .attr('x2', function (d) { return Math.max(r, Math.min(width - r, d.target.x)); })
                .attr('y2', function (d) { return Math.max(r, Math.min(height - r, d.target.y)); });
        });

        function dragstarted(d) {
            if (!d3.event.active) {
                simulation.alphaTarget(0.3)
                    .restart();
            }
            d.fx = d.x;
            d.fy = d.y;
        }

        function dragged(d) {
            d.fx = d3.event.x;
            d.fy = d3.event.y;
        }

        function dragended(d) {
            if (!d3.event.active) {
                simulation.alphaTarget(0);
            }
            d.fx = null;
            d.fy = null;
        }

    });
</script>