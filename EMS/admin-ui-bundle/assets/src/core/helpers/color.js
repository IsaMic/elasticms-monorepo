// color can be a hx string or an array of RGB values 0-255
function luma(color) {
  const rgb = typeof color === 'string' ? hexToRGBArray(color) : color
  return 0.2126 * rgb[0] + 0.7152 * rgb[1] + 0.0722 * rgb[2] // SMPTE C, Rec. 709 weightings
}

function hexToRGBArray(color) {
  if (color.length === 3) {
    color =
      color.charAt(0) +
      color.charAt(0) +
      color.charAt(1) +
      color.charAt(1) +
      color.charAt(2) +
      color.charAt(2)
  } else if (color.length !== 6) {
    throw new Error('Invalid hex color: ' + color)
  }
  const rgb = []
  for (let i = 0; i <= 2; i++) {
    rgb[i] = parseInt(color.substr(i * 2, 2), 16)
  }

  return rgb
}

export { luma, hexToRGBArray }
