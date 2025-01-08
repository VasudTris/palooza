const colors = {}; 
const defaultColor = '#ddd'; 
//! zorgt voor random kleuren van elke chart
function getRandomColor() {
    const letters = '89ABCDEF'; 
    let color = '#';
    for (let i = 0; i < 6; i++) {
        color += letters[Math.floor(Math.random() * letters.length)];
    }
    return color;
}


//! renderrt te chart
function renderChart(data) {
    if (!data) return;
    const resultsList = document.getElementById('resultsList');
    resultsList.innerHTML = ''; 

    const totalVotes = Object.values(data).reduce((a, b) => a + b, 0);

    for (const [label, count] of Object.entries(data)) {
        const container = document.createElement('div');
        container.className = 'bar-container';

        const labelDiv = document.createElement('div');
        labelDiv.className = 'bar-label';
        labelDiv.textContent = label;

        const bar = document.createElement('div');
        bar.className = 'bar';

        const fillDiv = document.createElement('div');
        fillDiv.className = 'bar-fill';
        fillDiv.style.width = `${(totalVotes === 0 ? 0 : (count / totalVotes) * 100)}%`;

        if (!colors[label]) {
            colors[label] = getRandomColor();
        }
        fillDiv.style.backgroundColor = colors[label]; 

        const valueDiv = document.createElement('div');
        valueDiv.className = 'bar-value';
        valueDiv.textContent = `${count} (${totalVotes === 0 ? 0 : Math.round((count / totalVotes) * 100)}%)`;

        bar.appendChild(fillDiv);
        container.appendChild(labelDiv);
        container.appendChild(bar);
        container.appendChild(valueDiv); 

        resultsList.appendChild(container);
    }
}

//! update chart
function updateChart() {
    fetchResults().then(data => {
        renderChart(data);
    })
}