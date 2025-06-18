class DesignCanvas {
  constructor() {
    this.canvas = document.getElementById("designCanvas")
    this.ctx = this.canvas.getContext("2d")
    this.elements = []
    this.selectedElement = null
    this.isDragging = false
    this.dragStart = { x: 0, y: 0 }
    this.history = []
    this.historyIndex = -1
    this.zoom = 1
    this.gridEnabled = false
    this.snapToGrid = true
    this.canvasSizes = {
      A4: { width: 794, height: 1123 },
      Post: { width: 1080, height: 1080 },
      Story: { width: 1080, height: 1920 },
      Banner: { width: 1200, height: 400 },
    }

    this.setupEventListeners()
    this.saveState()
  }

  setupEventListeners() {
    this.canvas.addEventListener("mousedown", this.handleMouseDown.bind(this))
    this.canvas.addEventListener("mousemove", this.handleMouseMove.bind(this))
    this.canvas.addEventListener("mouseup", this.handleMouseUp.bind(this))
    this.canvas.addEventListener("click", this.handleClick.bind(this))
  }

  handleMouseDown(e) {
    const rect = this.canvas.getBoundingClientRect()
    const x = e.clientX - rect.left
    const y = e.clientY - rect.top

    // Find clicked element
    for (let i = this.elements.length - 1; i >= 0; i--) {
      if (this.isPointInElement(x, y, this.elements[i])) {
        this.selectedElement = this.elements[i]
        this.isDragging = true
        this.dragStart = { x: x - this.selectedElement.x, y: y - this.selectedElement.y }
        break
      }
    }

    this.redraw()
  }

  handleMouseMove(e) {
    if (this.isDragging && this.selectedElement) {
      const rect = this.canvas.getBoundingClientRect()
      const x = e.clientX - rect.left
      const y = e.clientY - rect.top

      this.selectedElement.x = x - this.dragStart.x
      this.selectedElement.y = y - this.dragStart.y

      this.redraw()
    }
  }

  handleMouseUp(e) {
    if (this.isDragging) {
      this.isDragging = false
      this.saveState()
    }
  }

  handleClick(e) {
    this.redraw()
  }

  isPointInElement(x, y, element) {
    switch (element.type) {
      case "text":
        // Simple bounding box check for text
        return x >= element.x && x <= element.x + element.width && y >= element.y - element.height && y <= element.y
      case "rectangle":
        return x >= element.x && x <= element.x + element.width && y >= element.y && y <= element.y + element.height
      case "circle":
        const dx = x - (element.x + element.radius)
        const dy = y - (element.y + element.radius)
        return dx * dx + dy * dy <= element.radius * element.radius
      case "image":
        return x >= element.x && x <= element.x + element.width && y >= element.y && y <= element.y + element.height
    }
    return false
  }

  addText(text = "Sample Text") {
    const element = {
      type: "text",
      text: text,
      x: 100,
      y: 100,
      fontSize: 16,
      fontFamily: "Arial",
      color: "#000000",
      width: 100,
      height: 20,
    }

    this.elements.push(element)
    this.selectedElement = element
    this.redraw()
    this.saveState()
  }

  addImage(src) {
    const img = new Image()
    img.crossOrigin = "anonymous"
    img.onload = () => {
      const element = {
        type: "image",
        image: img,
        x: 50,
        y: 50,
        width: img.width > 200 ? 200 : img.width,
        height: img.height > 200 ? 200 : img.height,
        originalWidth: img.width,
        originalHeight: img.height,
      }

      this.elements.push(element)
      this.selectedElement = element
      this.redraw()
      this.saveState()
    }
    img.src = src
  }

  addShape(type) {
    let element

    if (type === "rectangle") {
      element = {
        type: "rectangle",
        x: 100,
        y: 100,
        width: 100,
        height: 60,
        fillColor: "#3498db",
        strokeColor: "#2980b9",
        strokeWidth: 2,
      }
    } else if (type === "circle") {
      element = {
        type: "circle",
        x: 100,
        y: 100,
        radius: 50,
        fillColor: "#e74c3c",
        strokeColor: "#c0392b",
        strokeWidth: 2,
      }
    }

    this.elements.push(element)
    this.selectedElement = element
    this.redraw()
    this.saveState()
  }

  updateSelectedText(property, value) {
    if (this.selectedElement && this.selectedElement.type === "text") {
      this.selectedElement[property] = value

      // Recalculate text dimensions
      this.ctx.font = `${this.selectedElement.fontSize}px ${this.selectedElement.fontFamily}`
      const metrics = this.ctx.measureText(this.selectedElement.text)
      this.selectedElement.width = metrics.width
      this.selectedElement.height = this.selectedElement.fontSize

      this.redraw()
      this.saveState()
    }
  }

  updateBackground(color) {
    this.canvas.style.backgroundColor = color
  }

  setCanvasSize(sizeKey) {
    const size = this.canvasSizes[sizeKey]
    if (size) {
      this.canvas.width = size.width
      this.canvas.height = size.height
      this.redraw()
      this.saveState()
    }
  }

  setZoom(zoomLevel) {
    this.zoom = zoomLevel
    this.redraw()
  }

  toggleGrid() {
    this.gridEnabled = !this.gridEnabled
    this.redraw()
  }

  drawGrid() {
    if (!this.gridEnabled) return
    const step = 20 * this.zoom
    this.ctx.save()
    this.ctx.strokeStyle = "#eee"
    this.ctx.lineWidth = 1
    for (let x = 0; x < this.canvas.width; x += step) {
      this.ctx.beginPath()
      this.ctx.moveTo(x, 0)
      this.ctx.lineTo(x, this.canvas.height)
      this.ctx.stroke()
    }
    for (let y = 0; y < this.canvas.height; y += step) {
      this.ctx.beginPath()
      this.ctx.moveTo(0, y)
      this.ctx.lineTo(this.canvas.width, y)
      this.ctx.stroke()
    }
    this.ctx.restore()
  }

  redraw() {
    // Clear canvas
    this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height)

    this.ctx.save()
    this.ctx.scale(this.zoom, this.zoom)
    this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height)
    this.drawGrid()

    // Draw all elements
    this.elements.forEach((element) => {
      this.drawElement(element)
    })

    // Draw selection indicator
    if (this.selectedElement) {
      this.drawSelection(this.selectedElement)
    }

    this.ctx.restore()
  }

  drawElement(element) {
    this.ctx.save()

    switch (element.type) {
      case "text":
        this.ctx.font = `${element.fontSize}px ${element.fontFamily}`
        this.ctx.fillStyle = element.color
        this.ctx.fillText(element.text, element.x, element.y)
        break

      case "rectangle":
        this.ctx.fillStyle = element.fillColor
        this.ctx.fillRect(element.x, element.y, element.width, element.height)
        this.ctx.strokeStyle = element.strokeColor
        this.ctx.lineWidth = element.strokeWidth
        this.ctx.strokeRect(element.x, element.y, element.width, element.height)
        break

      case "circle":
        this.ctx.beginPath()
        this.ctx.arc(element.x + element.radius, element.y + element.radius, element.radius, 0, 2 * Math.PI)
        this.ctx.fillStyle = element.fillColor
        this.ctx.fill()
        this.ctx.strokeStyle = element.strokeColor
        this.ctx.lineWidth = element.strokeWidth
        this.ctx.stroke()
        break

      case "image":
        this.ctx.drawImage(element.image, element.x, element.y, element.width, element.height)
        break
    }

    this.ctx.restore()
  }

  drawSelection(element) {
    this.ctx.save()
    this.ctx.strokeStyle = "#007bff"
    this.ctx.lineWidth = 2
    this.ctx.setLineDash([5, 5])

    const bounds = this.getElementBounds(element)
    this.ctx.strokeRect(bounds.x - 5, bounds.y - 5, bounds.width + 10, bounds.height + 10)

    this.ctx.restore()
  }

  getElementBounds(element) {
    switch (element.type) {
      case "text":
        return {
          x: element.x,
          y: element.y - element.height,
          width: element.width,
          height: element.height,
        }
      case "rectangle":
        return {
          x: element.x,
          y: element.y,
          width: element.width,
          height: element.height,
        }
      case "circle":
        return {
          x: element.x,
          y: element.y,
          width: element.radius * 2,
          height: element.radius * 2,
        }
      case "image":
        return {
          x: element.x,
          y: element.y,
          width: element.width,
          height: element.height,
        }
    }
  }

  saveState() {
    this.historyIndex++
    this.history = this.history.slice(0, this.historyIndex)
    this.history.push(JSON.stringify(this.elements))
  }

  undo() {
    if (this.historyIndex > 0) {
      this.historyIndex--
      this.elements = JSON.parse(this.history[this.historyIndex])
      this.selectedElement = null
      this.redraw()
    }
  }

  redo() {
    if (this.historyIndex < this.history.length - 1) {
      this.historyIndex++
      this.elements = JSON.parse(this.history[this.historyIndex])
      this.selectedElement = null
      this.redraw()
    }
  }

  clear() {
    this.elements = []
    this.selectedElement = null
    this.redraw()
    this.saveState()
  }

  getDesignData() {
    return {
      elements: this.elements,
      canvasWidth: this.canvas.width,
      canvasHeight: this.canvas.height,
      backgroundColor: this.canvas.style.backgroundColor,
    }
  }

  loadDesignData(data) {
    this.elements = data.elements || []
    this.canvas.width = data.canvasWidth || 800
    this.canvas.height = data.canvasHeight || 600
    this.canvas.style.backgroundColor = data.backgroundColor || "#ffffff"
    this.selectedElement = null
    this.redraw()
    this.saveState()
  }

  // Add to DesignCanvas class
  lockElement(index) {
    if (this.elements[index]) {
      this.elements[index].locked = !this.elements[index].locked;
      this.redraw();
    }
  }
  bringForward(index) {
    if (index < this.elements.length - 1) {
      [this.elements[index], this.elements[index + 1]] = [this.elements[index + 1], this.elements[index]];
      this.redraw();
    }
  }
  sendBackward(index) {
    if (index > 0) {
      [this.elements[index], this.elements[index - 1]] = [this.elements[index - 1], this.elements[index]];
      this.redraw();
    }
  }
}

// Initialize canvas
let designCanvas

document.addEventListener("DOMContentLoaded", () => {
  designCanvas = new DesignCanvas()
  loadCustomFonts()
})

// Add after DOMContentLoaded
function loadCustomFonts() {
  fetch('/fontFile/fonts.css')
    .then(res => res.text())
    .then(css => {
      // Extract font-family names
      const matches = [...css.matchAll(/font-family:\s*["']?([^;"']+)["']?/g)];
      const fontList = matches.map(m => m[1]);
      const fontSelect = document.getElementById("fontFamily");
      fontList.forEach(font => {
        if (![...fontSelect.options].some(opt => opt.value === font)) {
          const opt = document.createElement("option");
          opt.value = font;
          opt.textContent = font;
          fontSelect.appendChild(opt);
        }
      });
    });
}
document.addEventListener("DOMContentLoaded", loadCustomFonts);

// Tool functions
function addText() {
  const text = prompt("Enter text:") || "Sample Text"
  designCanvas.addText(text)
}

function addImage() {
  const fileInput = document.getElementById("imageUpload")
  const file = fileInput.files[0]

  if (file) {
    const reader = new FileReader()
    reader.onload = (e) => {
      designCanvas.addImage(e.target.result)
    }
    reader.readAsDataURL(file)
  }
}

function addShape(type) {
  designCanvas.addShape(type)
}

function updateFontSize() {
  const fontSize = document.getElementById("fontSize").value
  designCanvas.updateSelectedText("fontSize", Number.parseInt(fontSize))
}

function updateFontFamily() {
  const fontFamily = document.getElementById("fontFamily").value
  designCanvas.updateSelectedText("fontFamily", fontFamily)
}

function updateTextColor() {
  const color = document.getElementById("textColor").value
  designCanvas.updateSelectedText("color", color)
}

function updateBackground() {
  const color = document.getElementById("backgroundColor").value
  designCanvas.updateBackground(color)
}

function undo() {
  designCanvas.undo()
}

function redo() {
  designCanvas.redo()
}

function clearCanvas() {
  if (confirm("Are you sure you want to clear the canvas?")) {
    designCanvas.clear()
  }
}

function saveTemplate() {
  const modal = new bootstrap.Modal(document.getElementById("saveTemplateModal"))
  modal.show()
}

function confirmSaveTemplate() {
  const name = document.getElementById("templateName").value
  const categoryId = document.getElementById("templateCategory").value

  if (!name || !categoryId) {
    alert("Please fill in all fields")
    return
  }

  const designData = designCanvas.getDesignData()

  // Generate thumbnail
  const thumbnailCanvas = document.createElement("canvas")
  thumbnailCanvas.width = 200
  thumbnailCanvas.height = 150
  const thumbnailCtx = thumbnailCanvas.getContext("2d")

  // Scale down the main canvas to create thumbnail
  thumbnailCtx.drawImage(designCanvas.canvas, 0, 0, 200, 150)
  const thumbnail = thumbnailCanvas.toDataURL()

  // Send data to server
  fetch("save_template.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      name: name,
      category_id: categoryId,
      design_data: designData,
      thumbnail: thumbnail,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        alert("Template saved successfully!")
        const modalElement = document.getElementById("saveTemplateModal")
        const modal = bootstrap.Modal.getInstance(modalElement)
        modal.hide()
      } else {
        alert("Error saving template: " + data.message)
      }
    })
    .catch((error) => {
      console.error("Error:", error)
      alert("Error saving template")
    })
}

function previewTemplate() {
  const canvas = designCanvas.canvas
  const dataURL = canvas.toDataURL()

  const previewWindow = window.open("", "_blank")
  previewWindow.document.write(`
        <html>
            <head><title>Template Preview</title></head>
            <body style="margin: 0; padding: 20px; text-align: center;">
                <h2>Template Preview</h2>
                <img src="${dataURL}" style="border: 1px solid #ccc; max-width: 100%;">
                <br><br>
                <button onclick="window.close()">Close Preview</button>
            </body>
        </html>
    `)
}

function uploadFont() {
  const input = document.getElementById('fontUpload');
  const file = input.files[0];
  if (!file) return;
  const formData = new FormData();
  formData.append('font', file);
  fetch('/font-upload.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        alert('Font uploaded!');
        loadCustomFonts();
      } else {
        alert('Font upload failed.');
      }
    });
}

function exportAs(type) {
  let dataURL;
  if (type === 'png') dataURL = designCanvas.canvas.toDataURL('image/png');
  else if (type === 'jpg') dataURL = designCanvas.canvas.toDataURL('image/jpeg');
  else if (type === 'pdf') {
    // Use jsPDF library for PDF export
    const pdf = new jsPDF({
      orientation: designCanvas.canvas.width > designCanvas.canvas.height ? 'l' : 'p',
      unit: 'px',
      format: [designCanvas.canvas.width, designCanvas.canvas.height]
    });
    pdf.addImage(designCanvas.canvas.toDataURL('image/png'), 'PNG', 0, 0);
    pdf.save('design.pdf');
    return;
  }
  const link = document.createElement('a');
  link.href = dataURL;
  link.download = 'design.' + type;
  link.click();
}
