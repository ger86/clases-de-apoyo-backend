# Markup patterns for `content.html`, `figures.py` and `checks.py`

Taken from the approved example `example-2027-modelo-matematicas-ii/`. Copy these patterns exactly: the CSS lives in `template.html` and only knows these classes. Do not add `style` attributes except the ones shown here.

## 1. First page

```html
<div class="titulo">
  <div class="uni">UNIVERSIDADES PÚBLICAS DE LA COMUNIDAD DE MADRID<br>PRUEBA DE ACCESO A LA UNIVERSIDAD</div>
  <div class="curso">MODELO ORIENTATIVO Curso 2026/2027</div>
  <div class="materia">MATERIA: MATEMÁTICAS II</div>
</div>

<div class="instr-tit">INSTRUCCIONES GENERALES Y CALIFICACIÓN</div>
<p>Después de leer atentamente el examen, responda ... <b>Todas las respuestas deberán estar debidamente justificadas.</b><br>
<b>CALIFICACIÓN:</b> Cada pregunta se calificará sobre 2 puntos.<br>
<b>TIEMPO:</b> 90 minutos.</p>
```

## 2. Block heading

```html
<div class="bloque">Responda a las tres preguntas siguientes (calificación máxima por pregunta: 2 puntos):</div>
<div class="bloque new-page">Responda a una de las dos preguntas siguientes (calificación máxima: 2 puntos):</div>
```

`new-page` starts a new page. It also works on `<div class="enunciado avoid new-page">`. Right after a `bloque`, the statement takes `style="margin-top:0"`.

## 3. Statement, with parts and options

```html
<!-- ===================== PREGUNTA 3 ===================== -->
<div class="enunciado avoid">
  <p><span class="preg">Pregunta 3.</span> Texto del enunciado. Sea la función:</p>
  <div class="eq">\[f(x) = \frac{e^x - 1}{e^x + 1}.\]</div>
  <ol>
    <li><b class="l">a)</b><b>(1 punto)</b> Halle las asíntotas horizontales de \(f(x)\).</li>
    <li><b class="l">b)</b><b>(1 punto) Responda solo a uno de los dos apartados siguientes:</b></li>
  </ol>
  <div class="sub">
    <p><b>b1)</b> Calcular la probabilidad de ...</p>
    <p><b>b2)</b> Calcular la probabilidad de ...</p>
  </div>
</div>
```

Questions with options are numbered as in the exam (`Pregunta 4.1.`, `Pregunta 4.2.`).

## 4. Solution, parts, formulas, result, note

```html
<div class="sol">Solución.</div>
<div class="ap"><div class="lab">a.</div><div class="txt">
  <div class="fig-r"><img src="fig-q2.png" style="width:60mm" alt="Gráfica de f con sus asíntotas"></div>
  <p>Se piden las asíntotas horizontales, para lo cual se calcula el límite de \(f(x)\) cuando \(x \to +\infty\) y cuando \(x \to -\infty\).</p>
  <div class="eq">\[\lim_{x\to+\infty} \frac{e^x-1}{e^x+1} = \frac{\infty}{\infty} = \lim_{x\to+\infty} \frac{1 - e^{-x}}{1 + e^{-x}} = 1\]</div>
  <p class="res">Asíntotas horizontales: \(y = 1\) e \(y = -1\).</p>
  <p class="nota">* Como \(e^x - 1 &lt; e^x + 1\), se cumple \(-1 &lt; f(x) &lt; 1\) para todo \(x\).</p>
</div></div>
<div class="clear"></div>

<div class="ap avoid"><div class="lab">b1.</div><div class="txt">
  ...
</div></div>
```

- One `.ap` per part. Labels: `a.`, `b.`, `b1.`, `b2.`. Add `avoid` to a short part so it is not split between pages; leave it out on long parts.
- `.fig-r` is the first child of `.txt` (or sits before the first `.ap` when the figure serves the whole question). Width 54 to 62 mm. Put `<div class="clear"></div>` after the part when the figure is taller than its text.
- One formula per `.eq`. Join short steps in one line with `\ ; \quad`. A long chain goes in two `.eq` lines.
- Text introduced before the parts (definition of events, notation) is plain `<p>` between `Solución.` and the first `.ap`.

## 5. Two methods

```html
<p>El apartado se puede resolver de dos formas:</p>
<p><b>1ª</b> El numerador es el doble de la derivada del denominador, luego:</p>
<div class="eq">\[\int_0^{\ln 3} \frac{2e^x}{e^x+1}\,dx = \Big[\,2\ln\left(e^x+1\right)\Big]_0^{\ln 3} = 2\ln 4 - 2\ln 2 = 2\ln 2\]</div>
<p><b>2ª</b> Con el cambio de variable \(t = e^x + 1\), \(dt = e^x\,dx\). ...</p>
```

## 6. Cases of a parameter

```html
<ul class="casos">
  <li><b>Si \(a \neq 1\) y \(a \neq -2\):</b> \(|A| \neq 0 \Rightarrow \text{rg}\,A = \text{rg}\,A^* = 3 = \) n.º de incógnitas. <b>Sistema compatible determinado</b> (solución única).</li>
  <li><b>Si \(a = 1\):</b>
    \[A^* = \left(\begin{array}{ccc|c} 3 & 1 & 1 & 1 \\ 1 & -1 & 3 & 0 \\ 3 & 1 & 1 & 1 \end{array}\right)\]
    La primera y la tercera fila son iguales ... \(\text{rg}\,A = \text{rg}\,A^* = 2 &lt; 3\). <b>Sistema compatible indeterminado</b>.</li>
</ul>
```

Sub-cases inside a part that are not a list: `<p><b>En \(x = 2\):</b></p>` followed by `.eq` lines.

## 7. KaTeX patterns

Inline `\( \)`, display `\[ \]`, never `$`. In math write `&lt;` and `&gt;`. Decimal comma: `0{,}9733`. Units: `\text{ u}`, `\text{ u}^2`.

```latex
System:      \left\{\begin{array}{rcl} (a+2)x + y + z &=& a \\ x - ay + 3z &=& 0 \\ 3x + ay + z &=& 1 \end{array}\right.
Brace list:  \left\{\begin{array}{l} x = 1 - \lambda \\ y = 2\lambda \\ z = 2 + 2\lambda \end{array}\right. \quad \forall\,\lambda \in \mathbb R
Row step:    \xrightarrow{\ E_2 - E_1\ }
Matrix:      A = \begin{pmatrix} a+2 & 1 & 1 \\ 1 & -a & 3 \\ 3 & a & 1 \end{pmatrix}
Augmented:   A^* = \left(\begin{array}{ccc|c} a+2 & 1 & 1 & a \\ 1 & -a & 3 & 0 \\ 3 & a & 1 & 1 \end{array}\right)
Determinant: \begin{vmatrix} \vec i & \vec j & \vec k \\ 4 & 1 & 1 \\ -1 & 2 & 2 \end{vmatrix} = (0,-9,9)
Piecewise:   f(x) = \left\{\begin{array}{lll} 2x + 2 & \quad & \text{si } x \in [0,2] \\ ax^2 + bx & & \text{si } x \in (2,5) \end{array}\right.
Two roots:   a = \frac{-1\pm\sqrt{1+8}}{2} = \left\{\begin{array}{l} 1 \\ -2 \end{array}\right.
Side limit:  \lim_{x\to2^-} (2x+2) = 6
Barrow:      \int_2^4 \frac{2}{t}\,dt = \Big[\,2\ln t\,\Big]_2^4 = 2\ln 2
Inline int.: \displaystyle\int_0^{\ln(3)} \big(f(x)+1\big)\,dx
Vectors:     \overrightarrow{PQ} = (-1,2,2) \ ; \quad \vec u_r \ ; \quad \left|\overrightarrow{QR}\right| \ ; \quad \pi \equiv 4x + y + z - 6 = 0
Probability: P(\overline{A_1}\cap\overline{A_2}) = \frac13\cdot\frac15 = \frac{1}{15} \approx 0{,}0667
Table:       \begin{array}{ll} P(A_1) = \dfrac23 \quad & P(\overline{A_1}) = \dfrac13 \\[2mm] P(A_2) = \dfrac45 & P(\overline{A_2}) = \dfrac15 \end{array}
```

A left-aligned table of data: `<div class="eq" style="text-align:left;padding-left:20mm">\[ ... \]</div>`.

## 8. `figures.py`

One file makes all the figures. It runs inside `OUT`, saves `fig-<id>.png` and prints nothing but a final line.

```python
import numpy as np
import matplotlib
matplotlib.use("Agg")
import matplotlib.pyplot as plt

plt.rcParams.update({
    "font.family": "serif", "font.serif": ["Times New Roman", "Times", "DejaVu Serif"],
    "mathtext.fontset": "stix", "font.size": 11, "axes.linewidth": 0.8,
})
OUT = "fig-{}.png"
BLUE, RED = "#1f4e9c", "#b22222"


def axes_center(ax):
    ax.spines["left"].set_position("zero"); ax.spines["bottom"].set_position("zero")
    ax.spines["right"].set_visible(False); ax.spines["top"].set_visible(False)
    ax.tick_params(labelsize=9)


fig, ax = plt.subplots(figsize=(3.2, 2.3))            # 3.2 to 3.6 in wide, 2.3 to 3.6 in tall
xs = np.linspace(-5, 5, 400)
ax.plot(xs, np.tanh(xs / 2), color=BLUE, lw=1.3)      # main curve blue 1.3; second curve or tangent RED 0.9 to 1.1
xi = np.linspace(0, np.log(3), 100)
ax.fill_between(xi, 0, np.tanh(xi / 2) + 1, color=RED, alpha=0.18)   # shaded area
ax.axhline(1, ls="--", color="gray", lw=0.8)          # asymptotes dashed gray; guide lines ls=":" lw=0.7
ax.plot([0], [0], "ko", ms=3)                         # points
ax.text(-4.9, 1.1, "$y=1$", fontsize=9)               # labels 8 to 10 pt, placed by hand away from curves and axes
ax.set_ylim(-1.6, 2.3); axes_center(ax)
ax.set_xticks([-4, -2, 2, 4]); ax.set_yticks([-1, 1, 2])   # few ticks, never 0
fig.savefig(OUT.format("q2"), dpi=220, bbox_inches="tight"); plt.close(fig)
```

Sketches without axes (planes and lines, probability trees): `ax.axis("off")`, black lines `lw=0.6` to `0.8`, plane as `Polygon(..., fc="#eef3fb", ec="black", lw=0.8)`, probabilities on the branches in BLUE at 7.5 pt, events at 9 pt.

## 9. `checks.py`

Start with this helper. It accepts numbers, sympy expressions, tuples, lists, matrices, booleans and strings.

```python
from fractions import Fraction as F

import sympy as sp

ok_all = True


def check(name, ours, official="n/a"):
    """official: the value of the official solution, or the hand result written in the PDF when there is none."""
    global ok_all
    if isinstance(ours, sp.MatrixBase):
        ok = ours == sp.Matrix(official)
    elif isinstance(ours, (tuple, list, set, dict, bool, str)) or ours in (sp.true, sp.false):
        ok = ours == official
    else:
        ok = sp.simplify(sp.sympify(ours) - sp.sympify(official)) == 0
    ok_all &= bool(ok)
    print(f"{'OK ' if ok else 'BAD'} {name}: ours={ours} | official={official}")


x, a = sp.symbols("x a", real=True)
check("2b integral", sp.integrate(2 * sp.exp(x) / (sp.exp(x) + 1), (x, 0, sp.log(3))), 2 * sp.log(2))
check("3a P(llegar a tiempo)", 1 - F(1, 3) * F(1, 5) * F(2, 5), F(73, 75))
print("ALL OK" if ok_all else "SOME FAILED")
```

Decimals rounded as in the PDF are compared as `round(float(value), 4)` against the rounded number. A condition is checked as `bool(condition)` against `True`.
