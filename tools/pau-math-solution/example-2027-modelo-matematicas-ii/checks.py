"""Independent checks for the PAU Madrid 2027 modelo, Matemáticas II.
Each result is computed from the statement; the official value is printed next to it."""
from fractions import Fraction as F
from itertools import product

import sympy as sp

ok_all = True


def check(name, ours, official):
    global ok_all
    ok = sp.simplify(sp.sympify(ours) - sp.sympify(official)) == 0 if not isinstance(ours, (sp.MatrixBase, tuple, list, bool)) else ours == official
    ok_all &= bool(ok)
    print(f"{'OK ' if ok else 'BAD'} {name}: ours={ours} | official={official}")


x, y, z, t, s_, a, b, lam = sp.symbols('x y z t s a b lambda', real=True)

# ---------- Pregunta 1 ----------
P = sp.Matrix([1, 0, 2]); Q = sp.Matrix([0, 2, 4]); A = sp.Matrix([0, 4, 2])
u = Q - P
n = u.cross(A - P)
n = n / sp.gcd(list(n))
if n[0] < 0:
    n = -n
d = n.dot(P)
check("1a normal of plane", tuple(n), (4, 1, 1))
check("1a independent term", d, 6)
assert n.dot(Q) == d and n.dot(A) == d
# C = foot of perpendicular from A to r
tC = sp.solve((P + t * u - A).dot(u), t)[0]
C = P + tC * u
check("1a C", tuple(C), (0, 2, 4))
# second way: direction of s = n x u, intersect lines
v = n.cross(u)
sol = sp.solve(list(P + t * u - (A + s_ * v)), [t, s_], dict=True)[0]
check("1a C (second way)", tuple(P + sol[t] * u), (0, 2, 4))
check("1a r perp s", (C - A).dot(u), 0)
# b
dPQ = (Q - P).norm()
check("1b d(P,Q)", dPQ, 3)
w = sp.Matrix([0, 1, -1])
assert w.cross(A - C) == sp.zeros(3, 1)
mus = sp.solve(sp.Eq((w * t).norm() ** 2, 9), t)
Rs = [tuple(sp.simplify(e) for e in (C + m * w)) for m in mus]
print("    R points:", Rs)
for R in Rs:
    Rm = sp.Matrix(R)
    check("1b d(R,Q)", sp.simplify((Rm - Q).norm()), 3)
    check("1b R in plane", sp.simplify(n.dot(Rm) - d), 0)

# ---------- Pregunta 2 ----------
f = (sp.exp(x) - 1) / (sp.exp(x) + 1)
check("2a lim +oo", sp.limit(f, x, sp.oo), 1)
check("2a lim -oo", sp.limit(f, x, -sp.oo), -1)
check("2b f+1 rewrite", sp.simplify(f + 1 - 2 * sp.exp(x) / (sp.exp(x) + 1)), 0)
I = sp.integrate(f + 1, (x, 0, sp.log(3)))
check("2b integral", sp.simplify(I), 2 * sp.log(2))
print("    integral ~", sp.N(I))

# ---------- Pregunta 3 ----------
p = {1: F(2, 3), 2: F(4, 5), 3: F(3, 5)}
outcomes = {}
for o in product([True, False], repeat=3):
    pr = F(1)
    for i, caught in zip((1, 2, 3), o):
        pr *= p[i] if caught else 1 - p[i]
    outcomes[o] = pr
assert sum(outcomes.values()) == 1
pa = sum(pr for o, pr in outcomes.items() if any(o))
check("3a P(at least one)", pa, F(73, 75))
B = {o for o in outcomes if o[0] != o[2]}  # exactly one of L1, L3
pB = sum(outcomes[o] for o in B)
pnotL2_B = sum(outcomes[o] for o in B if not o[1])
check("3b1 P(B)", pB, F(7, 15))
check("3b1 P(no L2 | B)", pnotL2_B / pB, F(1, 5))
pb2 = sum(pr for o, pr in outcomes.items() if sum(o) == 2)
check("3b2 exactly two", pb2, F(34, 75))

# ---------- Pregunta 4.1 ----------
M = sp.Matrix([[a + 2, 1, 1], [1, -a, 3], [3, a, 1]])
bb = sp.Matrix([a, 0, 1])
detM = sp.expand(M.det())
check("4.1a det", detM, -4 * a**2 - 4 * a + 8)
check("4.1a roots", tuple(sorted(sp.solve(detM, a))), (-2, 1))
for av in (1, -2, 0, 5):
    Ma = M.subs(a, av); Aa = Ma.row_join(bb.subs(a, av))
    print(f"    a={av}: rank A={Ma.rank()}, rank A*={Aa.rank()}")
assert M.subs(a, 1).rank() == 2 and M.subs(a, 1).row_join(bb.subs(a, 1)).rank() == 2
assert M.subs(a, -2).rank() == 2 and M.subs(a, -2).row_join(bb.subs(a, -2)).rank() == 3
# a=-2 detail: rows
print("    a=-2 augmented:", M.subs(a, -2).row_join(bb.subs(a, -2)).tolist())
sol = sp.linsolve((M.subs(a, 1), bb.subs(a, 1)), [x, y, z])
print("    a=1 solution:", sol)
(xs, ys, zs), = sol
gen = (sp.Rational(1, 4) - lam, sp.Rational(1, 4) + 2 * lam, lam)
check("4.1b x", xs.subs(z, lam), gen[0])
check("4.1b y", ys.subs(z, lam), gen[1])
for row, rhs in zip(M.subs(a, 1).tolist(), bb.subs(a, 1)):
    assert sp.simplify(sum(c * g for c, g in zip(row, gen)) - rhs) == 0
# generic a solution by Cramer, for the record
gen_sol = sp.simplify(M.LUsolve(bb))
print("    generic solution (a != 1, -2):", [sp.factor(e) for e in gen_sol])

# ---------- Pregunta 4.2 ----------
A2 = sp.Matrix([[a, 1, 0], [1, a, 1], [0, 1, a]])
dA = sp.factor(A2.det())
check("4.2a det A", sp.expand(dA), a**3 - 2 * a)
check("4.2a roots", tuple(sorted(sp.solve(dA, a), key=float)), (-sp.sqrt(2), 0, sp.sqrt(2)))
A0 = A2.subs(a, 0)
check("4.2b A^2", A0**2, sp.Matrix([[1, 0, 1], [0, 2, 0], [1, 0, 1]]))
check("4.2b A^3 = 2A", A0**3 == 2 * A0, True)
check("4.2b A^9 = 16A", A0**9, 16 * A0)

# ---------- Pregunta 5.1 ----------
f1 = 2 * x + 2; f2 = a * x**2 + b * x; f3 = -(x - 5) ** 2 + 15
eqs = [sp.Eq(f2.subs(x, 2), f1.subs(x, 2)), sp.Eq(f2.subs(x, 5), f3.subs(x, 5))]
sab = sp.solve(eqs, [a, b])
check("5.1a a", sab[a], 0)
check("5.1a b", sab[b], 3)
L = sp.integrate(sp.sqrt(1 + sp.diff(f1, x) ** 2), (x, 0, 2))
check("5.1b length", L, 2 * sp.sqrt(5))
check("5.1b segment (0,2)-(2,6)", sp.sqrt(2**2 + 4**2), 2 * sp.sqrt(5))
print("    L ~", sp.N(L))

# ---------- Pregunta 5.2 ----------
g = (-2 * x + 1) / (x - 1)
gp = sp.simplify(sp.diff(g, x))
check("5.2a f'", gp, 1 / (x - 1) ** 2)
print("    f' zeros:", sp.solve(sp.numer(sp.together(gp)), x))
xs52 = sorted(sp.solve(sp.Eq(gp, 1), x))
check("5.2b x values", tuple(xs52), (0, 2))
pts = [(xv, g.subs(x, xv)) for xv in xs52]
check("5.2b points", tuple(pts), ((0, -1), (2, -3)))
print("    tangents:", [sp.expand(g.subs(x, xv) + (x - xv)) for xv in xs52])
print("    f(0) > f(2):", g.subs(x, 0) > g.subs(x, 2))

print("\nALL CHECKS PASSED" if ok_all else "\nSOME CHECKS FAILED")
