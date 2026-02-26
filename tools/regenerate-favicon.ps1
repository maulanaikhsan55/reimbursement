Add-Type -AssemblyName System.Drawing

$size = 32
$bmp = New-Object System.Drawing.Bitmap($size, $size)
$g = [System.Drawing.Graphics]::FromImage($bmp)
$g.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::AntiAlias
$g.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
$g.PixelOffsetMode = [System.Drawing.Drawing2D.PixelOffsetMode]::HighQuality
$g.Clear([System.Drawing.Color]::Transparent)

$rect = New-Object System.Drawing.Rectangle(1, 1, 30, 30)
$radius = 7
$path = New-Object System.Drawing.Drawing2D.GraphicsPath
$path.AddArc($rect.X, $rect.Y, $radius, $radius, 180, 90)
$path.AddArc($rect.Right - $radius, $rect.Y, $radius, $radius, 270, 90)
$path.AddArc($rect.Right - $radius, $rect.Bottom - $radius, $radius, $radius, 0, 90)
$path.AddArc($rect.X, $rect.Bottom - $radius, $radius, $radius, 90, 90)
$path.CloseFigure()

$bgBrush = New-Object System.Drawing.Drawing2D.LinearGradientBrush(
    ([System.Drawing.Point]::new(0, 0)),
    ([System.Drawing.Point]::new(32, 32)),
    ([System.Drawing.ColorTranslator]::FromHtml('#071833')),
    ([System.Drawing.ColorTranslator]::FromHtml('#5d8fd8'))
)
$g.FillPath($bgBrush, $path)

$topLight = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::FromArgb(36, 255, 255, 255))
$g.FillEllipse($topLight, 2, 1, 18, 11)

$shine = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::FromArgb(22, 255, 255, 255))
$g.FillPolygon($shine, @(
    [System.Drawing.PointF]::new(4, 4),
    [System.Drawing.PointF]::new(17, 5),
    [System.Drawing.PointF]::new(13, 11),
    [System.Drawing.PointF]::new(4, 12)
))

$border = New-Object System.Drawing.Pen([System.Drawing.Color]::FromArgb(78, 211, 228, 255), 1.0)
$g.DrawPath($border, $path)

$shadowPen = New-Object System.Drawing.Pen([System.Drawing.Color]::FromArgb(74, 5, 20, 47), 4.3)
$shadowPen.StartCap = [System.Drawing.Drawing2D.LineCap]::Round
$shadowPen.EndCap = [System.Drawing.Drawing2D.LineCap]::Round
$shadowPen.LineJoin = [System.Drawing.Drawing2D.LineJoin]::Round

$rPen = New-Object System.Drawing.Pen([System.Drawing.Color]::FromArgb(248, 250, 254, 255), 4.1)
$rPen.StartCap = [System.Drawing.Drawing2D.LineCap]::Round
$rPen.EndCap = [System.Drawing.Drawing2D.LineCap]::Round
$rPen.LineJoin = [System.Drawing.Drawing2D.LineJoin]::Round

$g.DrawLine($shadowPen, 11.9, 8.7, 11.9, 23.2)
$g.DrawArc($shadowPen, 11.9, 8.7, 10.0, 8.9, -90, 180)
$g.DrawLine($shadowPen, 16.9, 17.6, 21.4, 22.5)

$g.DrawLine($rPen, 11.0, 8.0, 11.0, 22.4)
$g.DrawArc($rPen, 11.0, 8.0, 10.0, 8.9, -90, 180)
$g.DrawLine($rPen, 15.9, 16.9, 20.3, 21.6)

$pngPath = 'public/favicon-32.png'
$bmp.Save($pngPath, [System.Drawing.Imaging.ImageFormat]::Png)

$pngBytes = [System.IO.File]::ReadAllBytes($pngPath)
$icoPath = 'public/favicon.ico'
$fs = New-Object System.IO.FileStream($icoPath, [System.IO.FileMode]::Create)
$bw = New-Object System.IO.BinaryWriter($fs)
$bw.Write([UInt16]0)
$bw.Write([UInt16]1)
$bw.Write([UInt16]1)
$bw.Write([Byte]32)
$bw.Write([Byte]32)
$bw.Write([Byte]0)
$bw.Write([Byte]0)
$bw.Write([UInt16]1)
$bw.Write([UInt16]32)
$bw.Write([UInt32]$pngBytes.Length)
$bw.Write([UInt32]22)
$bw.Write($pngBytes)
$bw.Close()
$fs.Close()

$rPen.Dispose()
$shadowPen.Dispose()
$border.Dispose()
$shine.Dispose()
$topLight.Dispose()
$bgBrush.Dispose()
$path.Dispose()
$g.Dispose()
$bmp.Dispose()

Write-Output 'FAVICON_REGENERATED'
