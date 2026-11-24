import numpy as np
import matplotlib
matplotlib.use("Agg")
import matplotlib.pyplot as plt
from matplotlib.patches import Polygon

plt.rcParams.update({
    "font.family": "serif", "font.serif": ["Times New Roman", "Times", "DejaVu Serif"],
    "mathtext.fontset": "stix", "font.size": 11, "axes.linewidth": 0.8,
})
OUT = "fig-{}.png"


def axes_center(ax):
    ax.spines["left"].set_position("zero"); ax.spines["bottom"].set_position("zero")
    ax.spines["right"].set_visible(False); ax.spines["top"].set_visible(False)
    ax.tick_params(labelsize=9)


# ---- Q1: plane with r and s perpendicular at C = Q ----
fig, ax = plt.subplots(figsize=(3.2, 2.4))
ax.add_patch(Polygon([[0, 0], [7, 0], [9, 3.2], [2, 3.2]], closed=True, fc="#eef3fb", ec="black", lw=0.8))
C = np.array([4.5, 1.6])
dr = np.array([1, 0.35]); dr /= np.linalg.norm(dr)
ds = np.array([-0.35, 1]); ds /= np.linalg.norm(ds)
ax.plot(*np.array([C - 3.6 * dr, C + 3.0 * dr]).T, color="#1f4e9c", lw=1.2)
ax.plot(*np.array([C - 1.6 * ds, C + 1.6 * ds]).T, color="#b22222", lw=1.2)
P = C - 2.4 * dr; A = C + 1.0 * ds
R1 = C + 1.45 * ds; R2 = C - 1.45 * ds
for pt, lab, off in [(P, "$P$", (-0.1, -0.45)), (C, "$C=Q$", (0.2, -0.45)), (A, "$A$", (0.2, -0.05)),
                     (R1, "$R_1$", (0.2, 0.0)), (R2, "$R_2$", (0.2, -0.15))]:
    ax.plot(*pt, "ko", ms=3)
    ax.text(pt[0] + off[0], pt[1] + off[1], lab, fontsize=10)
sq = 0.28
ax.plot(*np.array([C + sq * dr, C + sq * dr + sq * ds, C + sq * ds]).T, color="black", lw=0.6)
ax.text(C[0] + 3.0 * dr[0] + 0.1, C[1] + 3.0 * dr[1], "$r$", color="#1f4e9c")
ax.text(*(C - 1.6 * ds + np.array([-0.45, 0.05])), "$s$", color="#b22222")
ax.text(0.5, 0.25, r"$\pi$", fontsize=12)
ax.set_xlim(-0.2, 9.3); ax.set_ylim(-0.2, 3.5); ax.axis("off"); ax.set_aspect("equal")
fig.savefig(OUT.format("q1"), dpi=220, bbox_inches="tight"); plt.close(fig)

# ---- Q2: f(x) = (e^x - 1)/(e^x + 1) with asymptotes and the area of f+1 ----
fig, ax = plt.subplots(figsize=(3.2, 2.3))
xs = np.linspace(-5, 5, 400)
f = (np.exp(xs) - 1) / (np.exp(xs) + 1)
ax.plot(xs, f, color="#1f4e9c", lw=1.3, label=r"$f(x)$")
ax.plot(xs, f + 1, color="#b22222", lw=1.1, label=r"$f(x)+1$")
xi = np.linspace(0, np.log(3), 100)
ax.fill_between(xi, 0, (np.exp(xi) - 1) / (np.exp(xi) + 1) + 1, color="#b22222", alpha=0.18)
for yv in (1, -1):
    ax.axhline(yv, ls="--", color="gray", lw=0.8)
ax.text(-4.9, 1.1, "$y=1$", fontsize=9); ax.text(2.6, -1.35, "$y=-1$", fontsize=9)
ax.set_ylim(-1.6, 2.3); axes_center(ax)
ax.set_xticks([-4, -2, 2, 4]); ax.set_yticks([-1, 1, 2])
ax.legend(fontsize=8, loc="upper left", frameon=False, bbox_to_anchor=(0.0, 0.93))
fig.savefig(OUT.format("q2"), dpi=220, bbox_inches="tight"); plt.close(fig)

# ---- Q3: probability tree ----
fig, ax = plt.subplots(figsize=(3.6, 3.6))
ax.axis("off")
p = [("A_1", "2/3"), ("A_2", "4/5"), ("A_3", "3/5")]
q = ["1/3", "1/5", "2/5"]
xs_lv = [0, 1, 2, 3]
leaves = []


def draw(level, y, ylo, yhi, path):
    if level == 3:
        leaves.append((y, path)); return
    for k, (lab, pr, sign) in enumerate([(p[level][0], p[level][1], ""), (r"\overline{" + p[level][0] + "}", q[level], "c")]):
        ny = (ylo + (yhi - ylo) * (0.75 if k == 0 else 0.25))
        nlo, nhi = (ylo + (yhi - ylo) / 2, yhi) if k == 0 else (ylo, ylo + (yhi - ylo) / 2)
        ax.plot([xs_lv[level] + (0.25 if level else 0.0), xs_lv[level + 1] - 0.08], [y, ny], color="black", lw=0.6)
        ax.text(xs_lv[level + 1] - 0.05, ny, f"${lab}$", fontsize=9, ha="left", va="center")
        mx, my = (xs_lv[level] + xs_lv[level + 1]) / 2, (y + ny) / 2
        ax.text(mx - 0.05, my + (0.18 if k == 0 else -0.18), f"${pr}$", fontsize=7.5, ha="center", va="center", color="#1f4e9c")
        draw(level + 1, ny, nlo, nhi, path + [k == 0])


draw(0, 4, 0, 8, [])
ax.set_xlim(-0.1, 3.5); ax.set_ylim(-0.2, 8.2)
fig.savefig(OUT.format("q3"), dpi=220, bbox_inches="tight"); plt.close(fig)

# ---- Q5.1: piecewise function, [0,2] highlighted ----
fig, ax = plt.subplots(figsize=(3.2, 2.5))
x1 = np.linspace(0, 2, 50); x2 = np.linspace(2, 5, 80); x3 = np.linspace(5, 7, 60)
ax.plot(x1, 2 * x1 + 2, color="#b22222", lw=2.0)
ax.plot(x2, 3 * x2, color="#1f4e9c", lw=1.3)
ax.plot(x3, -(x3 - 5) ** 2 + 15, color="#1f4e9c", lw=1.3)
ax.plot([0, 2, 5, 7], [2, 6, 15, 11], "ko", ms=3)
ax.text(0.15, 3.6, "$L$", color="#b22222", fontsize=11)
for xv, yv, t in [(2, 6, "(2, 6)"), (5, 15, "(5, 15)"), (0, 2, "(0, 2)")]:
    ax.text(xv + 0.15, yv - 1.4, t, fontsize=8)
for xv, yv in [(2, 6), (5, 15)]:
    ax.plot([xv, xv], [0, yv], ls=":", color="gray", lw=0.7)
ax.set_xlim(-0.3, 7.5); ax.set_ylim(0, 17); axes_center(ax)
ax.set_xticks([1, 2, 3, 4, 5, 6, 7]); ax.set_yticks([5, 10, 15])
fig.savefig(OUT.format("q51"), dpi=220, bbox_inches="tight"); plt.close(fig)

# ---- Q5.2: f(x) = (-2x+1)/(x-1) with tangent lines of slope 1 ----
fig, ax = plt.subplots(figsize=(3.2, 2.7))
for xx in (np.linspace(-4, 0.88, 200), np.linspace(1.12, 5, 200)):
    ax.plot(xx, (-2 * xx + 1) / (xx - 1), color="#1f4e9c", lw=1.3)
xt = np.linspace(-3, 4.5, 10)
ax.plot(xt, xt - 1, color="#b22222", lw=0.9); ax.plot(xt, xt - 5, color="#b22222", lw=0.9)
ax.axvline(1, ls="--", color="gray", lw=0.8); ax.axhline(-2, ls="--", color="gray", lw=0.8)
ax.plot([0, 2], [-1, -3], "ko", ms=3)
ax.text(-1.6, -0.6, "$(0,-1)$", fontsize=8); ax.text(2.15, -3.6, "$(2,-3)$", fontsize=8)
ax.text(3.1, 2.6, "$y=x-1$", fontsize=8, color="#b22222"); ax.text(-1.2, -6.8, "$y=x-5$", fontsize=8, color="#b22222")
ax.text(1.1, 5.3, "$x=1$", fontsize=8); ax.text(-3.9, -2.6, "$y=-2$", fontsize=8)
ax.set_xlim(-4, 5); ax.set_ylim(-7, 6); axes_center(ax)
ax.set_xticks([-2, 2, 4]); ax.set_yticks([-6, -4, 2, 4])
fig.savefig(OUT.format("q52"), dpi=220, bbox_inches="tight"); plt.close(fig)
print("figures done")
