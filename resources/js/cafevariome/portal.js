const canvas = document.getElementById("similarity-graph");
const ctx = canvas.getContext("2d");

const getRandomInRange = (min, max) => {
    return Math.random() * (max - min) + min;
};

const TOTAL_POINTS = 30;
const CONNECT_DISTANCE = 75;

let points = [];

const drawPoint = point => {
    ctx.zIndex = 20;
    // ctx.fillStyle = 'rgba(182, 112, 9, 0.8)';
    ctx.fillStyle = '#FE5F55';
    ctx.strokeWidth = 5;
    ctx.beginPath();
    ctx.arc(point.x, point.y, 5, 0, 2 * Math.PI);
    ctx.fill();
};

const movePoint = point => {
    point.x += point.s * Math.cos(point.a);
    point.y += point.s * Math.sin(point.a);
};

const distance = (point, other) => {
    return Math.sqrt((other.x - point.x) ** 2 + (other.y - point.y) ** 2);
};

const drawLine = (point, other, d) => {
    ctx.zIndex = 10;
    ctx.beginPath();
    ctx.moveTo(point.x, point.y);
    ctx.lineTo(other.x, other.y);
    ctx.strokeStyle = `rgba(234,145,23, ${Math.abs(d / CONNECT_DISTANCE - 1)})`;
    ctx.stroke();
};

const loop = () => {
    window.requestAnimationFrame(loop);

    for (let i = points.length; i < TOTAL_POINTS; i++) {
        points.push({
            x: getRandomInRange(0, canvas.width),
            y: getRandomInRange(0, canvas.height),
            a: getRandomInRange(0, 360),
            s: 0.01
        });
    }

    ctx.clearRect(0, 0, canvas.width, canvas.height);

    points.forEach(point => {
        movePoint(point);
    });

    points = points.filter(point => {
        return (
            point.x >= 0 &&
            point.x < canvas.width &&
            point.y >= 0 &&
            point.y < canvas.height
        );
    });

    points.forEach(point => {
        drawPoint(point);
    });

    points.forEach(point => {
        points.forEach(other => {
            if (point === other) {
                return;
            }

            const d = distance(point, other);
            if (d < CONNECT_DISTANCE) {
                drawLine(point, other, d);
            }
        });
    });
};

loop();

const ontologyGraph = document.getElementById('ontology-graph');
const graphDesc = document.getElementById('heading-ontology-description');

ontologyGraph.addEventListener('mouseenter', ()=>{
    graphDesc.setAttribute('opacity', '1');
});

ontologyGraph.addEventListener('mouseleave', ()=>{
    graphDesc.setAttribute('opacity', '0');
});

const medicalImages = document.getElementById('medical-images');
const medicalImagesDesc = document.getElementById('medical-image-description');

medicalImages.addEventListener('mouseenter', ()=>{
    medicalImagesDesc.setAttribute('opacity', '1');
});

medicalImages.addEventListener('mouseleave', ()=>{
    medicalImagesDesc.setAttribute('opacity', '0');
});

const genomicData = document.getElementById('genomic-data');
const genomicDataDesc = document.getElementById('dna-helix-description');

genomicData.addEventListener('mouseenter', ()=>{
    genomicDataDesc.setAttribute('opacity', '1');
});

genomicData.addEventListener('mouseleave', ()=>{
    genomicDataDesc.setAttribute('opacity', '0');
});

const discoveryIcon = document.getElementById('discovery-icon');
const discoveryIconDesc = document.getElementById('discovery-description');

discoveryIcon.addEventListener('mouseenter', ()=>{
    discoveryIconDesc.setAttribute('opacity', '1');
});

discoveryIcon.addEventListener('mouseleave', ()=>{
    discoveryIconDesc.setAttribute('opacity', '0');
});
