const UF = () => Promise.resolve().then(() => zF), { Fragment: w1, jsx: P, jsxs: k } = globalThis.__GLOBALS__.ReactJSXRuntime;
"use" in globalThis.__GLOBALS__.React || (globalThis.__GLOBALS__.React.use = () => {
  throw new Error("`use` is not available in this version of React. Make currently only supports React 18, but `use` is only available in React 19+.");
});
function ES(e) {
  return globalThis.__GLOBALS__.React.isValidElement(e) && e.props && "_fgT" in e.props;
}
function Ta(e) {
  return globalThis.__GLOBALS__.React.isValidElement(e) && e.type === "fg-txt";
}
function TS(e) {
  const { _fgT: t, _fgS: r, _fgB: n, _fgD: a, ...i } = e.props;
  return globalThis.__GLOBALS__.React.createElement(t, {
    ...i,
    key: e.key
  }, i.children);
}
function Hi(e) {
  return ES(e) ? TS(e) : Ta(e) ? e.props.children : e;
}
const va = globalThis.__GLOBALS__.React.Children, $r = {
  map(e, t, r) {
    return va.map(e, (n, a) => {
      const i = Hi(n);
      return Ta(n) ? null : t.call(r, i, a);
    });
  },
  forEach(e, t, r) {
    va.forEach(e, (n, a) => {
      if (Ta(n))
        return;
      const i = Hi(n);
      t.call(r, i, a);
    });
  },
  count(e) {
    let t = 0;
    return va.forEach(e, (r) => {
      Ta(r) || t++;
    }), t;
  },
  toArray(e) {
    const t = [];
    return va.forEach(e, (r) => {
      Ta(r) || t.push(Hi(r));
    }), t;
  },
  only(e) {
    const t = va.only(e);
    return Hi(t);
  }
}, M = globalThis.__GLOBALS__.React, { cloneElement: Ue, Component: hu, createContext: Ge, createElement: ue, createFactory: MS, createRef: jS, forwardRef: Ir, Fragment: Rn, isValidElement: Lt, lazy: NS, memo: O1, Profiler: CS, PureComponent: br, startTransition: Ba, StrictMode: $S, Suspense: RS, use: kS, useCallback: dn, useContext: se, useDebugValue: IS, useDeferredValue: DS, useEffect: It, useId: LS, useImperativeHandle: _1, useInsertionEffect: qS, useLayoutEffect: Ah, useMemo: ft, useReducer: BS, useRef: pr, useState: Oe, useSyncExternalStore: FS, useTransition: zS, version: US } = globalThis.__GLOBALS__.React, WS = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  Children: $r,
  Component: hu,
  Fragment: Rn,
  Profiler: CS,
  PureComponent: br,
  StrictMode: $S,
  Suspense: RS,
  cloneElement: Ue,
  createContext: Ge,
  createElement: ue,
  createFactory: MS,
  createRef: jS,
  default: M,
  forwardRef: Ir,
  isValidElement: Lt,
  lazy: NS,
  memo: O1,
  startTransition: Ba,
  use: kS,
  useCallback: dn,
  useContext: se,
  useDebugValue: IS,
  useDeferredValue: DS,
  useEffect: It,
  useId: LS,
  useImperativeHandle: _1,
  useInsertionEffect: qS,
  useLayoutEffect: Ah,
  useMemo: ft,
  useReducer: BS,
  useRef: pr,
  useState: Oe,
  useSyncExternalStore: FS,
  useTransition: zS,
  version: US
}, Symbol.toStringTag, { value: "Module" }));
/**
 * react-router v7.13.0
 *
 * Copyright (c) Remix Software Inc.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE.md file in the root directory of this source tree.
 *
 * @license MIT
 */
var S1 = (e) => {
  throw TypeError(e);
}, HS = (e, t, r) => t.has(e) || S1("Cannot " + r), Zu = (e, t, r) => (HS(e, t, "read from private field"), r ? r.call(e) : t.get(e)), GS = (e, t, r) => t.has(e) ? S1("Cannot add the same private member more than once") : t instanceof WeakSet ? t.add(e) : t.set(e, r), nv = "popstate";
function KS(e = {}) {
  function t(n, a) {
    let { pathname: i, search: o, hash: u } = n.location;
    return Fa(
      "",
      { pathname: i, search: o, hash: u },
      // state defaults to `null` because `window.history.state` does
      a.state && a.state.usr || null,
      a.state && a.state.key || "default"
    );
  }
  function r(n, a) {
    return typeof a == "string" ? a : Yt(a);
  }
  return XS(
    t,
    r,
    null,
    e
  );
}
function he(e, t) {
  if (e === !1 || e === null || typeof e > "u")
    throw new Error(t);
}
function We(e, t) {
  if (!e) {
    typeof console < "u" && console.warn(t);
    try {
      throw new Error(t);
    } catch {
    }
  }
}
function VS() {
  return Math.random().toString(36).substring(2, 10);
}
function av(e, t) {
  return {
    usr: e.state,
    key: e.key,
    idx: t
  };
}
function Fa(e, t, r = null, n) {
  return {
    pathname: typeof e == "string" ? e : e.pathname,
    search: "",
    hash: "",
    ...typeof t == "string" ? Dr(t) : t,
    state: r,
    // TODO: This could be cleaned up.  push/replace should probably just take
    // full Locations now and avoid the need to run through this flow at all
    // But that's a pretty big refactor to the current test suite so going to
    // keep as is for the time being and just let any incoming keys take precedence
    key: t && t.key || n || VS()
  };
}
function Yt({
  pathname: e = "/",
  search: t = "",
  hash: r = ""
}) {
  return t && t !== "?" && (e += t.charAt(0) === "?" ? t : "?" + t), r && r !== "#" && (e += r.charAt(0) === "#" ? r : "#" + r), e;
}
function Dr(e) {
  let t = {};
  if (e) {
    let r = e.indexOf("#");
    r >= 0 && (t.hash = e.substring(r), e = e.substring(0, r));
    let n = e.indexOf("?");
    n >= 0 && (t.search = e.substring(n), e = e.substring(0, n)), e && (t.pathname = e);
  }
  return t;
}
function XS(e, t, r, n = {}) {
  let { window: a = document.defaultView, v5Compat: i = !1 } = n, o = a.history, u = "POP", l = null, s = f();
  s == null && (s = 0, o.replaceState({ ...o.state, idx: s }, ""));
  function f() {
    return (o.state || { idx: null }).idx;
  }
  function c() {
    u = "POP";
    let p = f(), g = p == null ? null : p - s;
    s = p, l && l({ action: u, location: v.location, delta: g });
  }
  function d(p, g) {
    u = "PUSH";
    let b = Fa(v.location, p, g);
    s = f() + 1;
    let w = av(b, s), _ = v.createHref(b);
    try {
      o.pushState(w, "", _);
    } catch (m) {
      if (m instanceof DOMException && m.name === "DataCloneError")
        throw m;
      a.location.assign(_);
    }
    i && l && l({ action: u, location: v.location, delta: 1 });
  }
  function h(p, g) {
    u = "REPLACE";
    let b = Fa(v.location, p, g);
    s = f();
    let w = av(b, s), _ = v.createHref(b);
    o.replaceState(w, "", _), i && l && l({ action: u, location: v.location, delta: 0 });
  }
  function y(p) {
    return P1(p);
  }
  let v = {
    get action() {
      return u;
    },
    get location() {
      return e(a, o);
    },
    listen(p) {
      if (l)
        throw new Error("A history only accepts one active listener");
      return a.addEventListener(nv, c), l = p, () => {
        a.removeEventListener(nv, c), l = null;
      };
    },
    createHref(p) {
      return t(a, p);
    },
    createURL: y,
    encodeLocation(p) {
      let g = y(p);
      return {
        pathname: g.pathname,
        search: g.search,
        hash: g.hash
      };
    },
    push: d,
    replace: h,
    go(p) {
      return o.go(p);
    }
  };
  return v;
}
function P1(e, t = !1) {
  let r = "http://localhost";
  typeof window < "u" && (r = window.location.origin !== "null" ? window.location.origin : window.location.href), he(r, "No window.location.(origin|href) available to create URL");
  let n = typeof e == "string" ? e : Yt(e);
  return n = n.replace(/ $/, "%20"), !t && n.startsWith("//") && (n = r + n), new URL(n, r);
}
var Ma, iv = class {
  /**
   * Create a new `RouterContextProvider` instance
   * @param init An optional initial context map to populate the provider with
   */
  constructor(e) {
    if (GS(this, Ma, /* @__PURE__ */ new Map()), e)
      for (let [t, r] of e)
        this.set(t, r);
  }
  /**
   * Access a value from the context. If no value has been set for the context,
   * it will return the context's `defaultValue` if provided, or throw an error
   * if no `defaultValue` was set.
   * @param context The context to get the value for
   * @returns The value for the context, or the context's `defaultValue` if no
   * value was set
   */
  get(e) {
    if (Zu(this, Ma).has(e))
      return Zu(this, Ma).get(e);
    if (e.defaultValue !== void 0)
      return e.defaultValue;
    throw new Error("No value found for context");
  }
  /**
   * Set a value for the context. If the context already has a value set, this
   * will overwrite it.
   *
   * @param context The context to set the value for
   * @param value The value to set for the context
   * @returns {void}
   */
  set(e, t) {
    Zu(this, Ma).set(e, t);
  }
};
Ma = /* @__PURE__ */ new WeakMap();
var YS = /* @__PURE__ */ new Set([
  "lazy",
  "caseSensitive",
  "path",
  "id",
  "index",
  "children"
]);
function ZS(e) {
  return YS.has(
    e
  );
}
var JS = /* @__PURE__ */ new Set([
  "lazy",
  "caseSensitive",
  "path",
  "id",
  "index",
  "middleware",
  "children"
]);
function QS(e) {
  return JS.has(
    e
  );
}
function eP(e) {
  return e.index === !0;
}
function za(e, t, r = [], n = {}, a = !1) {
  return e.map((i, o) => {
    let u = [...r, String(o)], l = typeof i.id == "string" ? i.id : u.join("-");
    if (he(
      i.index !== !0 || !i.children,
      "Cannot specify children on an index route"
    ), he(
      a || !n[l],
      `Found a route id collision on id "${l}".  Route id's must be globally unique within Data Router usages`
    ), eP(i)) {
      let s = {
        ...i,
        id: l
      };
      return n[l] = ov(
        s,
        t(s)
      ), s;
    } else {
      let s = {
        ...i,
        id: l,
        children: void 0
      };
      return n[l] = ov(
        s,
        t(s)
      ), i.children && (s.children = za(
        i.children,
        t,
        u,
        n,
        a
      )), s;
    }
  });
}
function ov(e, t) {
  return Object.assign(e, {
    ...t,
    ...typeof t.lazy == "object" && t.lazy != null ? {
      lazy: {
        ...e.lazy,
        ...t.lazy
      }
    } : {}
  });
}
function Er(e, t, r = "/") {
  return ja(e, t, r, !1);
}
function ja(e, t, r, n) {
  let a = typeof t == "string" ? Dr(t) : t, i = Mt(a.pathname || "/", r);
  if (i == null)
    return null;
  let o = A1(e);
  rP(o);
  let u = null;
  for (let l = 0; u == null && l < o.length; ++l) {
    let s = hP(i);
    u = fP(
      o[l],
      s,
      n
    );
  }
  return u;
}
function tP(e, t) {
  let { route: r, pathname: n, params: a } = e;
  return {
    id: r.id,
    pathname: n,
    params: a,
    data: t[r.id],
    loaderData: t[r.id],
    handle: r.handle
  };
}
function A1(e, t = [], r = [], n = "", a = !1) {
  let i = (o, u, l = a, s) => {
    let f = {
      relativePath: s === void 0 ? o.path || "" : s,
      caseSensitive: o.caseSensitive === !0,
      childrenIndex: u,
      route: o
    };
    if (f.relativePath.startsWith("/")) {
      if (!f.relativePath.startsWith(n) && l)
        return;
      he(
        f.relativePath.startsWith(n),
        `Absolute route path "${f.relativePath}" nested under path "${n}" is not valid. An absolute child route path must start with the combined path of all its parent routes.`
      ), f.relativePath = f.relativePath.slice(n.length);
    }
    let c = Kt([n, f.relativePath]), d = r.concat(f);
    o.children && o.children.length > 0 && (he(
      // Our types know better, but runtime JS may not!
      // @ts-expect-error
      o.index !== !0,
      `Index routes must not have child routes. Please remove all child routes from route path "${c}".`
    ), A1(
      o.children,
      t,
      d,
      c,
      l
    )), !(o.path == null && !o.index) && t.push({
      path: c,
      score: sP(c, o.index),
      routesMeta: d
    });
  };
  return e.forEach((o, u) => {
    if (o.path === "" || !o.path?.includes("?"))
      i(o, u);
    else
      for (let l of E1(o.path))
        i(o, u, !0, l);
  }), t;
}
function E1(e) {
  let t = e.split("/");
  if (t.length === 0) return [];
  let [r, ...n] = t, a = r.endsWith("?"), i = r.replace(/\?$/, "");
  if (n.length === 0)
    return a ? [i, ""] : [i];
  let o = E1(n.join("/")), u = [];
  return u.push(
    ...o.map(
      (l) => l === "" ? i : [i, l].join("/")
    )
  ), a && u.push(...o), u.map(
    (l) => e.startsWith("/") && l === "" ? "/" : l
  );
}
function rP(e) {
  e.sort(
    (t, r) => t.score !== r.score ? r.score - t.score : cP(
      t.routesMeta.map((n) => n.childrenIndex),
      r.routesMeta.map((n) => n.childrenIndex)
    )
  );
}
var nP = /^:[\w-]+$/, aP = 3, iP = 2, oP = 1, uP = 10, lP = -2, uv = (e) => e === "*";
function sP(e, t) {
  let r = e.split("/"), n = r.length;
  return r.some(uv) && (n += lP), t && (n += iP), r.filter((a) => !uv(a)).reduce(
    (a, i) => a + (nP.test(i) ? aP : i === "" ? oP : uP),
    n
  );
}
function cP(e, t) {
  return e.length === t.length && e.slice(0, -1).every((n, a) => n === t[a]) ? (
    // If two routes are siblings, we should try to match the earlier sibling
    // first. This allows people to have fine-grained control over the matching
    // behavior by simply putting routes with identical paths in the order they
    // want them tried.
    e[e.length - 1] - t[t.length - 1]
  ) : (
    // Otherwise, it doesn't really make sense to rank non-siblings by index,
    // so they sort equally.
    0
  );
}
function fP(e, t, r = !1) {
  let { routesMeta: n } = e, a = {}, i = "/", o = [];
  for (let u = 0; u < n.length; ++u) {
    let l = n[u], s = u === n.length - 1, f = i === "/" ? t : t.slice(i.length) || "/", c = ho(
      { path: l.relativePath, caseSensitive: l.caseSensitive, end: s },
      f
    ), d = l.route;
    if (!c && s && r && !n[n.length - 1].route.index && (c = ho(
      {
        path: l.relativePath,
        caseSensitive: l.caseSensitive,
        end: !1
      },
      f
    )), !c)
      return null;
    Object.assign(a, c.params), o.push({
      // TODO: Can this as be avoided?
      params: a,
      pathname: Kt([i, c.pathname]),
      pathnameBase: yP(
        Kt([i, c.pathnameBase])
      ),
      route: d
    }), c.pathnameBase !== "/" && (i = Kt([i, c.pathnameBase]));
  }
  return o;
}
function ho(e, t) {
  typeof e == "string" && (e = { path: e, caseSensitive: !1, end: !0 });
  let [r, n] = dP(
    e.path,
    e.caseSensitive,
    e.end
  ), a = t.match(r);
  if (!a) return null;
  let i = a[0], o = i.replace(/(.)\/+$/, "$1"), u = a.slice(1);
  return {
    params: n.reduce(
      (s, { paramName: f, isOptional: c }, d) => {
        if (f === "*") {
          let y = u[d] || "";
          o = i.slice(0, i.length - y.length).replace(/(.)\/+$/, "$1");
        }
        const h = u[d];
        return c && !h ? s[f] = void 0 : s[f] = (h || "").replace(/%2F/g, "/"), s;
      },
      {}
    ),
    pathname: i,
    pathnameBase: o,
    pattern: e
  };
}
function dP(e, t = !1, r = !0) {
  We(
    e === "*" || !e.endsWith("*") || e.endsWith("/*"),
    `Route path "${e}" will be treated as if it were "${e.replace(/\*$/, "/*")}" because the \`*\` character must always follow a \`/\` in the pattern. To get rid of this warning, please change the route path to "${e.replace(/\*$/, "/*")}".`
  );
  let n = [], a = "^" + e.replace(/\/*\*?$/, "").replace(/^\/*/, "/").replace(/[\\.*+^${}|()[\]]/g, "\\$&").replace(
    /\/:([\w-]+)(\?)?/g,
    (o, u, l) => (n.push({ paramName: u, isOptional: l != null }), l ? "/?([^\\/]+)?" : "/([^\\/]+)")
  ).replace(/\/([\w-]+)\?(\/|$)/g, "(/$1)?$2");
  return e.endsWith("*") ? (n.push({ paramName: "*" }), a += e === "*" || e === "/*" ? "(.*)$" : "(?:\\/(.+)|\\/*)$") : r ? a += "\\/*$" : e !== "" && e !== "/" && (a += "(?:(?=\\/|$))"), [new RegExp(a, t ? void 0 : "i"), n];
}
function hP(e) {
  try {
    return e.split("/").map((t) => decodeURIComponent(t).replace(/\//g, "%2F")).join("/");
  } catch (t) {
    return We(
      !1,
      `The URL path "${e}" could not be decoded because it is a malformed URL segment. This is probably due to a bad percent encoding (${t}).`
    ), e;
  }
}
function Mt(e, t) {
  if (t === "/") return e;
  if (!e.toLowerCase().startsWith(t.toLowerCase()))
    return null;
  let r = t.endsWith("/") ? t.length - 1 : t.length, n = e.charAt(r);
  return n && n !== "/" ? null : e.slice(r) || "/";
}
function pP({
  basename: e,
  pathname: t
}) {
  return t === "/" ? e : Kt([e, t]);
}
var T1 = /^(?:[a-z][a-z0-9+.-]*:|\/\/)/i, Eh = (e) => T1.test(e);
function vP(e, t = "/") {
  let {
    pathname: r,
    search: n = "",
    hash: a = ""
  } = typeof e == "string" ? Dr(e) : e, i;
  return r ? (r = r.replace(/\/\/+/g, "/"), r.startsWith("/") ? i = lv(r.substring(1), "/") : i = lv(r, t)) : i = t, {
    pathname: i,
    search: mP(n),
    hash: gP(a)
  };
}
function lv(e, t) {
  let r = t.replace(/\/+$/, "").split("/");
  return e.split("/").forEach((a) => {
    a === ".." ? r.length > 1 && r.pop() : a !== "." && r.push(a);
  }), r.length > 1 ? r.join("/") : "/";
}
function Ju(e, t, r, n) {
  return `Cannot include a '${e}' character in a manually specified \`to.${t}\` field [${JSON.stringify(
    n
  )}].  Please separate it out to the \`to.${r}\` field. Alternatively you may provide the full path as a string in <Link to="..."> and the router will parse it for you.`;
}
function M1(e) {
  return e.filter(
    (t, r) => r === 0 || t.route.path && t.route.path.length > 0
  );
}
function Th(e) {
  let t = M1(e);
  return t.map(
    (r, n) => n === t.length - 1 ? r.pathname : r.pathnameBase
  );
}
function Mh(e, t, r, n = !1) {
  let a;
  typeof e == "string" ? a = Dr(e) : (a = { ...e }, he(
    !a.pathname || !a.pathname.includes("?"),
    Ju("?", "pathname", "search", a)
  ), he(
    !a.pathname || !a.pathname.includes("#"),
    Ju("#", "pathname", "hash", a)
  ), he(
    !a.search || !a.search.includes("#"),
    Ju("#", "search", "hash", a)
  ));
  let i = e === "" || a.pathname === "", o = i ? "/" : a.pathname, u;
  if (o == null)
    u = r;
  else {
    let c = t.length - 1;
    if (!n && o.startsWith("..")) {
      let d = o.split("/");
      for (; d[0] === ".."; )
        d.shift(), c -= 1;
      a.pathname = d.join("/");
    }
    u = c >= 0 ? t[c] : "/";
  }
  let l = vP(a, u), s = o && o !== "/" && o.endsWith("/"), f = (i || o === ".") && r.endsWith("/");
  return !l.pathname.endsWith("/") && (s || f) && (l.pathname += "/"), l;
}
var Kt = (e) => e.join("/").replace(/\/\/+/g, "/"), yP = (e) => e.replace(/\/+$/, "").replace(/^\/*/, "/"), mP = (e) => !e || e === "?" ? "" : e.startsWith("?") ? e : "?" + e, gP = (e) => !e || e === "#" ? "" : e.startsWith("#") ? e : "#" + e, Ai = class {
  constructor(e, t, r, n = !1) {
    this.status = e, this.statusText = t || "", this.internal = n, r instanceof Error ? (this.data = r.toString(), this.error = r) : this.data = r;
  }
};
function Ua(e) {
  return e != null && typeof e.status == "number" && typeof e.statusText == "string" && typeof e.internal == "boolean" && "data" in e;
}
function Ei(e) {
  return e.map((t) => t.route.path).filter(Boolean).join("/").replace(/\/\/*/g, "/") || "/";
}
var j1 = typeof window < "u" && typeof window.document < "u" && typeof window.document.createElement < "u";
function N1(e, t) {
  let r = e;
  if (typeof r != "string" || !T1.test(r))
    return {
      absoluteURL: void 0,
      isExternal: !1,
      to: r
    };
  let n = r, a = !1;
  if (j1)
    try {
      let i = new URL(window.location.href), o = r.startsWith("//") ? new URL(i.protocol + r) : new URL(r), u = Mt(o.pathname, t);
      o.origin === i.origin && u != null ? r = u + o.search + o.hash : a = !0;
    } catch {
      We(
        !1,
        `<Link to="${r}"> contains an invalid URL which will probably break when clicked - please update to a valid URL path.`
      );
    }
  return {
    absoluteURL: n,
    isExternal: a,
    to: r
  };
}
var jr = Symbol("Uninstrumented");
function bP(e, t) {
  let r = {
    lazy: [],
    "lazy.loader": [],
    "lazy.action": [],
    "lazy.middleware": [],
    middleware: [],
    loader: [],
    action: []
  };
  e.forEach(
    (a) => a({
      id: t.id,
      index: t.index,
      path: t.path,
      instrument(i) {
        let o = Object.keys(r);
        for (let u of o)
          i[u] && r[u].push(i[u]);
      }
    })
  );
  let n = {};
  if (typeof t.lazy == "function" && r.lazy.length > 0) {
    let a = An(r.lazy, t.lazy, () => {
    });
    a && (n.lazy = a);
  }
  if (typeof t.lazy == "object") {
    let a = t.lazy;
    ["middleware", "loader", "action"].forEach((i) => {
      let o = a[i], u = r[`lazy.${i}`];
      if (typeof o == "function" && u.length > 0) {
        let l = An(u, o, () => {
        });
        l && (n.lazy = Object.assign(n.lazy || {}, {
          [i]: l
        }));
      }
    });
  }
  return ["loader", "action"].forEach((a) => {
    let i = t[a];
    if (typeof i == "function" && r[a].length > 0) {
      let o = i[jr] ?? i, u = An(
        r[a],
        o,
        (...l) => sv(l[0])
      );
      u && (a === "loader" && o.hydrate === !0 && (u.hydrate = !0), u[jr] = o, n[a] = u);
    }
  }), t.middleware && t.middleware.length > 0 && r.middleware.length > 0 && (n.middleware = t.middleware.map((a) => {
    let i = a[jr] ?? a, o = An(
      r.middleware,
      i,
      (...u) => sv(u[0])
    );
    return o ? (o[jr] = i, o) : a;
  })), n;
}
function xP(e, t) {
  let r = {
    navigate: [],
    fetch: []
  };
  if (t.forEach(
    (n) => n({
      instrument(a) {
        let i = Object.keys(a);
        for (let o of i)
          a[o] && r[o].push(a[o]);
      }
    })
  ), r.navigate.length > 0) {
    let n = e.navigate[jr] ?? e.navigate, a = An(
      r.navigate,
      n,
      (...i) => {
        let [o, u] = i;
        return {
          to: typeof o == "number" || typeof o == "string" ? o : o ? Yt(o) : ".",
          ...cv(e, u ?? {})
        };
      }
    );
    a && (a[jr] = n, e.navigate = a);
  }
  if (r.fetch.length > 0) {
    let n = e.fetch[jr] ?? e.fetch, a = An(r.fetch, n, (...i) => {
      let [o, , u, l] = i;
      return {
        href: u ?? ".",
        fetcherKey: o,
        ...cv(e, l ?? {})
      };
    });
    a && (a[jr] = n, e.fetch = a);
  }
  return e;
}
function An(e, t, r) {
  return e.length === 0 ? null : async (...n) => {
    let a = await C1(
      e,
      r(...n),
      () => t(...n),
      e.length - 1
    );
    if (a.type === "error")
      throw a.value;
    return a.value;
  };
}
async function C1(e, t, r, n) {
  let a = e[n], i;
  if (a) {
    let o, u = async () => (o ? console.error("You cannot call instrumented handlers more than once") : o = C1(e, t, r, n - 1), i = await o, he(i, "Expected a result"), i.type === "error" && i.value instanceof Error ? { status: "error", error: i.value } : { status: "success", error: void 0 });
    try {
      await a(u, t);
    } catch (l) {
      console.error("An instrumentation function threw an error:", l);
    }
    o || await u(), await o;
  } else
    try {
      i = { type: "success", value: await r() };
    } catch (o) {
      i = { type: "error", value: o };
    }
  return i || {
    type: "error",
    value: new Error("No result assigned in instrumentation chain.")
  };
}
function sv(e) {
  let { request: t, context: r, params: n, unstable_pattern: a } = e;
  return {
    request: wP(t),
    params: { ...n },
    unstable_pattern: a,
    context: OP(r)
  };
}
function cv(e, t) {
  return {
    currentUrl: Yt(e.state.location),
    ..."formMethod" in t ? { formMethod: t.formMethod } : {},
    ..."formEncType" in t ? { formEncType: t.formEncType } : {},
    ..."formData" in t ? { formData: t.formData } : {},
    ..."body" in t ? { body: t.body } : {}
  };
}
function wP(e) {
  return {
    method: e.method,
    url: e.url,
    headers: {
      get: (...t) => e.headers.get(...t)
    }
  };
}
function OP(e) {
  if (SP(e)) {
    let t = { ...e };
    return Object.freeze(t), t;
  } else
    return {
      get: (t) => e.get(t)
    };
}
var _P = Object.getOwnPropertyNames(Object.prototype).sort().join("\0");
function SP(e) {
  if (e === null || typeof e != "object")
    return !1;
  const t = Object.getPrototypeOf(e);
  return t === Object.prototype || t === null || Object.getOwnPropertyNames(t).sort().join("\0") === _P;
}
var $1 = [
  "POST",
  "PUT",
  "PATCH",
  "DELETE"
], PP = new Set(
  $1
), AP = [
  "GET",
  ...$1
], EP = new Set(AP), R1 = /* @__PURE__ */ new Set([301, 302, 303, 307, 308]), TP = /* @__PURE__ */ new Set([307, 308]), Qu = {
  state: "idle",
  location: void 0,
  formMethod: void 0,
  formAction: void 0,
  formEncType: void 0,
  formData: void 0,
  json: void 0,
  text: void 0
}, MP = {
  state: "idle",
  data: void 0,
  formMethod: void 0,
  formAction: void 0,
  formEncType: void 0,
  formData: void 0,
  json: void 0,
  text: void 0
}, ya = {
  state: "unblocked",
  proceed: void 0,
  reset: void 0,
  location: void 0
}, jP = (e) => ({
  hasErrorBoundary: !!e.hasErrorBoundary
}), k1 = "remix-router-transitions", I1 = Symbol("ResetLoaderData");
function NP(e) {
  const t = e.window ? e.window : typeof window < "u" ? window : void 0, r = typeof t < "u" && typeof t.document < "u" && typeof t.document.createElement < "u";
  he(
    e.routes.length > 0,
    "You must provide a non-empty routes array to createRouter"
  );
  let n = e.hydrationRouteProperties || [], a = e.mapRouteProperties || jP, i = a;
  if (e.unstable_instrumentations) {
    let E = e.unstable_instrumentations;
    i = (j) => ({
      ...a(j),
      ...bP(
        E.map((I) => I.route).filter(Boolean),
        j
      )
    });
  }
  let o = {}, u = za(
    e.routes,
    i,
    void 0,
    o
  ), l, s = e.basename || "/";
  s.startsWith("/") || (s = `/${s}`);
  let f = e.dataStrategy || IP, c = {
    ...e.future
  }, d = null, h = /* @__PURE__ */ new Set(), y = null, v = null, p = null, g = e.hydrationData != null, b = Er(u, e.history.location, s), w = !1, _ = null, m;
  if (b == null && !e.patchRoutesOnNavigation) {
    let E = St(404, {
      pathname: e.history.location.pathname
    }), { matches: j, route: I } = Gi(u);
    m = !0, b = j, _ = { [I.id]: E };
  } else if (b && !e.hydrationData && Bi(
    b,
    u,
    e.history.location.pathname
  ).active && (b = null), b)
    if (b.some((E) => E.route.lazy))
      m = !1;
    else if (!b.some((E) => jh(E.route)))
      m = !0;
    else {
      let E = e.hydrationData ? e.hydrationData.loaderData : null, j = e.hydrationData ? e.hydrationData.errors : null;
      if (j) {
        let I = b.findIndex(
          (U) => j[U.route.id] !== void 0
        );
        m = b.slice(0, I + 1).every(
          (U) => !id(U.route, E, j)
        );
      } else
        m = b.every(
          (I) => !id(I.route, E, j)
        );
    }
  else {
    m = !1, b = [];
    let E = Bi(
      null,
      u,
      e.history.location.pathname
    );
    E.active && E.matches && (w = !0, b = E.matches);
  }
  let O, x = {
    historyAction: e.history.action,
    location: e.history.location,
    matches: b,
    initialized: m,
    navigation: Qu,
    // Don't restore on initial updateState() if we were SSR'd
    restoreScrollPosition: e.hydrationData != null ? !1 : null,
    preventScrollReset: !1,
    revalidation: "idle",
    loaderData: e.hydrationData && e.hydrationData.loaderData || {},
    actionData: e.hydrationData && e.hydrationData.actionData || null,
    errors: e.hydrationData && e.hydrationData.errors || _,
    fetchers: /* @__PURE__ */ new Map(),
    blockers: /* @__PURE__ */ new Map()
  }, S = "POP", T = null, C = !1, A, N = !1, $ = /* @__PURE__ */ new Map(), D = null, R = !1, L = !1, z = /* @__PURE__ */ new Set(), F = /* @__PURE__ */ new Map(), W = 0, X = -1, J = /* @__PURE__ */ new Map(), G = /* @__PURE__ */ new Set(), Q = /* @__PURE__ */ new Map(), de = /* @__PURE__ */ new Map(), ge = /* @__PURE__ */ new Set(), qe = /* @__PURE__ */ new Map(), bt, Fe = null;
  function V() {
    if (d = e.history.listen(
      ({ action: E, location: j, delta: I }) => {
        if (bt) {
          bt(), bt = void 0;
          return;
        }
        We(
          qe.size === 0 || I != null,
          "You are trying to use a blocker on a POP navigation to a location that was not created by @remix-run/router. This will fail silently in production. This can happen if you are navigating outside the router via `window.history.pushState`/`window.location.hash` instead of using router navigation APIs.  This can also happen if you are using createHashRouter and the user manually changes the URL."
        );
        let U = Jp({
          currentLocation: x.location,
          nextLocation: j,
          historyAction: E
        });
        if (U && I != null) {
          let K = new Promise((te) => {
            bt = te;
          });
          e.history.go(I * -1), qi(U, {
            state: "blocked",
            location: j,
            proceed() {
              qi(U, {
                state: "proceeding",
                proceed: void 0,
                reset: void 0,
                location: j
              }), K.then(() => e.history.go(I));
            },
            reset() {
              let te = new Map(x.blockers);
              te.set(U, ya), B({ blockers: te });
            }
          }), T?.resolve(), T = null;
          return;
        }
        return je(E, j);
      }
    ), r) {
      eA(t, $);
      let E = () => tA(t, $);
      t.addEventListener("pagehide", E), D = () => t.removeEventListener("pagehide", E);
    }
    return x.initialized || je("POP", x.location, {
      initialHydration: !0
    }), O;
  }
  function le() {
    d && d(), D && D(), h.clear(), A && A.abort(), x.fetchers.forEach((E, j) => Vu(j)), x.blockers.forEach((E, j) => Zp(j));
  }
  function ce(E) {
    return h.add(E), () => h.delete(E);
  }
  function B(E, j = {}) {
    E.matches && (E.matches = E.matches.map((K) => {
      let te = o[K.route.id], Z = K.route;
      return Z.element !== te.element || Z.errorElement !== te.errorElement || Z.hydrateFallbackElement !== te.hydrateFallbackElement ? {
        ...K,
        route: te
      } : K;
    })), x = {
      ...x,
      ...E
    };
    let I = [], U = [];
    x.fetchers.forEach((K, te) => {
      K.state === "idle" && (ge.has(te) ? I.push(te) : U.push(te));
    }), ge.forEach((K) => {
      !x.fetchers.has(K) && !F.has(K) && I.push(K);
    }), [...h].forEach(
      (K) => K(x, {
        deletedFetchers: I,
        newErrors: E.errors ?? null,
        viewTransitionOpts: j.viewTransitionOpts,
        flushSync: j.flushSync === !0
      })
    ), I.forEach((K) => Vu(K)), U.forEach((K) => x.fetchers.delete(K));
  }
  function Ee(E, j, { flushSync: I } = {}) {
    let U = x.actionData != null && x.navigation.formMethod != null && it(x.navigation.formMethod) && x.navigation.state === "loading" && E.state?._isRedirect !== !0, K;
    j.actionData ? Object.keys(j.actionData).length > 0 ? K = j.actionData : K = null : U ? K = x.actionData : K = null;
    let te = j.loaderData ? xv(
      x.loaderData,
      j.loaderData,
      j.matches || [],
      j.errors
    ) : x.loaderData, Z = x.blockers;
    Z.size > 0 && (Z = new Map(Z), Z.forEach((oe, ne) => Z.set(ne, ya)));
    let ee = R ? !1 : ev(E, j.matches || x.matches), re = C === !0 || x.navigation.formMethod != null && it(x.navigation.formMethod) && E.state?._isRedirect !== !0;
    l && (u = l, l = void 0), R || S === "POP" || (S === "PUSH" ? e.history.push(E, E.state) : S === "REPLACE" && e.history.replace(E, E.state));
    let ae;
    if (S === "POP") {
      let oe = $.get(x.location.pathname);
      oe && oe.has(E.pathname) ? ae = {
        currentLocation: x.location,
        nextLocation: E
      } : $.has(E.pathname) && (ae = {
        currentLocation: E,
        nextLocation: x.location
      });
    } else if (N) {
      let oe = $.get(x.location.pathname);
      oe ? oe.add(E.pathname) : (oe = /* @__PURE__ */ new Set([E.pathname]), $.set(x.location.pathname, oe)), ae = {
        currentLocation: x.location,
        nextLocation: E
      };
    }
    B(
      {
        ...j,
        // matches, errors, fetchers go through as-is
        actionData: K,
        loaderData: te,
        historyAction: S,
        location: E,
        initialized: !0,
        navigation: Qu,
        revalidation: "idle",
        restoreScrollPosition: ee,
        preventScrollReset: re,
        blockers: Z
      },
      {
        viewTransitionOpts: ae,
        flushSync: I === !0
      }
    ), S = "POP", C = !1, N = !1, R = !1, L = !1, T?.resolve(), T = null, Fe?.resolve(), Fe = null;
  }
  async function ye(E, j) {
    if (T?.resolve(), T = null, typeof E == "number") {
      T || (T = Sv());
      let we = T.promise;
      return e.history.go(E), we;
    }
    let I = ad(
      x.location,
      x.matches,
      s,
      E,
      j?.fromRouteId,
      j?.relative
    ), { path: U, submission: K, error: te } = fv(
      !1,
      I,
      j
    ), Z = x.location, ee = Fa(x.location, U, j && j.state);
    ee = {
      ...ee,
      ...e.history.encodeLocation(ee)
    };
    let re = j && j.replace != null ? j.replace : void 0, ae = "PUSH";
    re === !0 ? ae = "REPLACE" : re === !1 || K != null && it(K.formMethod) && K.formAction === x.location.pathname + x.location.search && (ae = "REPLACE");
    let oe = j && "preventScrollReset" in j ? j.preventScrollReset === !0 : void 0, ne = (j && j.flushSync) === !0, xe = Jp({
      currentLocation: Z,
      nextLocation: ee,
      historyAction: ae
    });
    if (xe) {
      qi(xe, {
        state: "blocked",
        location: ee,
        proceed() {
          qi(xe, {
            state: "proceeding",
            proceed: void 0,
            reset: void 0,
            location: ee
          }), ye(E, j);
        },
        reset() {
          let we = new Map(x.blockers);
          we.set(xe, ya), B({ blockers: we });
        }
      });
      return;
    }
    await je(ae, ee, {
      submission: K,
      // Send through the formData serialization error if we have one so we can
      // render at the right error boundary after we match routes
      pendingError: te,
      preventScrollReset: oe,
      replace: j && j.replace,
      enableViewTransition: j && j.viewTransition,
      flushSync: ne,
      callSiteDefaultShouldRevalidate: j && j.unstable_defaultShouldRevalidate
    });
  }
  function Be() {
    Fe || (Fe = Sv()), Ku(), B({ revalidation: "loading" });
    let E = Fe.promise;
    return x.navigation.state === "submitting" ? E : x.navigation.state === "idle" ? (je(x.historyAction, x.location, {
      startUninterruptedRevalidation: !0
    }), E) : (je(
      S || x.historyAction,
      x.navigation.location,
      {
        overrideNavigation: x.navigation,
        // Proxy through any rending view transition
        enableViewTransition: N === !0
      }
    ), E);
  }
  async function je(E, j, I) {
    A && A.abort(), A = null, S = E, R = (I && I.startUninterruptedRevalidation) === !0, _S(x.location, x.matches), C = (I && I.preventScrollReset) === !0, N = (I && I.enableViewTransition) === !0;
    let U = l || u, K = I && I.overrideNavigation, te = I?.initialHydration && x.matches && x.matches.length > 0 && !w ? (
      // `matchRoutes()` has already been called if we're in here via `router.initialize()`
      x.matches
    ) : Er(U, j, s), Z = (I && I.flushSync) === !0;
    if (te && x.initialized && !L && WP(x.location, j) && !(I && I.submission && it(I.submission.formMethod))) {
      Ee(j, { matches: te }, { flushSync: Z });
      return;
    }
    let ee = Bi(te, U, j.pathname);
    if (ee.active && ee.matches && (te = ee.matches), !te) {
      let { error: Ye, notFoundMatches: lt, route: Te } = Xu(
        j.pathname
      );
      Ee(
        j,
        {
          matches: lt,
          loaderData: {},
          errors: {
            [Te.id]: Ye
          }
        },
        { flushSync: Z }
      );
      return;
    }
    A = new AbortController();
    let re = Pn(
      e.history,
      j,
      A.signal,
      I && I.submission
    ), ae = e.getContext ? await e.getContext() : new iv(), oe;
    if (I && I.pendingError)
      oe = [
        Tr(te).route.id,
        { type: "error", error: I.pendingError }
      ];
    else if (I && I.submission && it(I.submission.formMethod)) {
      let Ye = await rt(
        re,
        j,
        I.submission,
        te,
        ae,
        ee.active,
        I && I.initialHydration === !0,
        { replace: I.replace, flushSync: Z }
      );
      if (Ye.shortCircuited)
        return;
      if (Ye.pendingActionResult) {
        let [lt, Te] = Ye.pendingActionResult;
        if (yt(Te) && Ua(Te.error) && Te.error.status === 404) {
          A = null, Ee(j, {
            matches: Ye.matches,
            loaderData: {},
            errors: {
              [lt]: Te.error
            }
          });
          return;
        }
      }
      te = Ye.matches || te, oe = Ye.pendingActionResult, K = el(j, I.submission), Z = !1, ee.active = !1, re = Pn(
        e.history,
        re.url,
        re.signal
      );
    }
    let {
      shortCircuited: ne,
      matches: xe,
      loaderData: we,
      errors: Qe
    } = await zt(
      re,
      j,
      te,
      ae,
      ee.active,
      K,
      I && I.submission,
      I && I.fetcherSubmission,
      I && I.replace,
      I && I.initialHydration === !0,
      Z,
      oe,
      I && I.callSiteDefaultShouldRevalidate
    );
    ne || (A = null, Ee(j, {
      matches: xe || te,
      ...wv(oe),
      loaderData: we,
      errors: Qe
    }));
  }
  async function rt(E, j, I, U, K, te, Z, ee = {}) {
    Ku();
    let re = JP(j, I);
    if (B({ navigation: re }, { flushSync: ee.flushSync === !0 }), te) {
      let ne = await Fi(
        U,
        j.pathname,
        E.signal
      );
      if (ne.type === "aborted")
        return { shortCircuited: !0 };
      if (ne.type === "error") {
        if (ne.partialMatches.length === 0) {
          let { matches: we, route: Qe } = Gi(u);
          return {
            matches: we,
            pendingActionResult: [
              Qe.id,
              {
                type: "error",
                error: ne.error
              }
            ]
          };
        }
        let xe = Tr(ne.partialMatches).route.id;
        return {
          matches: ne.partialMatches,
          pendingActionResult: [
            xe,
            {
              type: "error",
              error: ne.error
            }
          ]
        };
      } else if (ne.matches)
        U = ne.matches;
      else {
        let { notFoundMatches: xe, error: we, route: Qe } = Xu(
          j.pathname
        );
        return {
          matches: xe,
          pendingActionResult: [
            Qe.id,
            {
              type: "error",
              error: we
            }
          ]
        };
      }
    }
    let ae, oe = so(U, j);
    if (!oe.route.action && !oe.route.lazy)
      ae = {
        type: "error",
        error: St(405, {
          method: E.method,
          pathname: j.pathname,
          routeId: oe.route.id
        })
      };
    else {
      let ne = jn(
        i,
        o,
        E,
        U,
        oe,
        Z ? [] : n,
        K
      ), xe = await _r(
        E,
        ne,
        K,
        null
      );
      if (ae = xe[oe.route.id], !ae) {
        for (let we of U)
          if (xe[we.route.id]) {
            ae = xe[we.route.id];
            break;
          }
      }
      if (E.signal.aborted)
        return { shortCircuited: !0 };
    }
    if (Yr(ae)) {
      let ne;
      return ee && ee.replace != null ? ne = ee.replace : ne = mv(
        ae.response.headers.get("Location"),
        new URL(E.url),
        s,
        e.history
      ) === x.location.pathname + x.location.search, await Ut(E, ae, !0, {
        submission: I,
        replace: ne
      }), { shortCircuited: !0 };
    }
    if (yt(ae)) {
      let ne = Tr(U, oe.route.id);
      return (ee && ee.replace) !== !0 && (S = "PUSH"), {
        matches: U,
        pendingActionResult: [
          ne.route.id,
          ae,
          oe.route.id
        ]
      };
    }
    return {
      matches: U,
      pendingActionResult: [oe.route.id, ae]
    };
  }
  async function zt(E, j, I, U, K, te, Z, ee, re, ae, oe, ne, xe) {
    let we = te || el(j, Z), Qe = Z || ee || _v(we), Ye = !R && !ae;
    if (K) {
      if (Ye) {
        let nt = er(ne);
        B(
          {
            navigation: we,
            ...nt !== void 0 ? { actionData: nt } : {}
          },
          {
            flushSync: oe
          }
        );
      }
      let be = await Fi(
        I,
        j.pathname,
        E.signal
      );
      if (be.type === "aborted")
        return { shortCircuited: !0 };
      if (be.type === "error") {
        if (be.partialMatches.length === 0) {
          let { matches: xn, route: Gr } = Gi(u);
          return {
            matches: xn,
            loaderData: {},
            errors: {
              [Gr.id]: be.error
            }
          };
        }
        let nt = Tr(be.partialMatches).route.id;
        return {
          matches: be.partialMatches,
          loaderData: {},
          errors: {
            [nt]: be.error
          }
        };
      } else if (be.matches)
        I = be.matches;
      else {
        let { error: nt, notFoundMatches: xn, route: Gr } = Xu(
          j.pathname
        );
        return {
          matches: xn,
          loaderData: {},
          errors: {
            [Gr.id]: nt
          }
        };
      }
    }
    let lt = l || u, { dsMatches: Te, revalidatingFetchers: xt } = dv(
      E,
      U,
      i,
      o,
      e.history,
      x,
      I,
      Qe,
      j,
      ae ? [] : n,
      ae === !0,
      L,
      z,
      ge,
      Q,
      G,
      lt,
      s,
      e.patchRoutesOnNavigation != null,
      ne,
      xe
    );
    if (X = ++W, !e.dataStrategy && !Te.some((be) => be.shouldLoad) && !Te.some(
      (be) => be.route.middleware && be.route.middleware.length > 0
    ) && xt.length === 0) {
      let be = Xp();
      return Ee(
        j,
        {
          matches: I,
          loaderData: {},
          // Commit pending error if we're short circuiting
          errors: ne && yt(ne[1]) ? { [ne[0]]: ne[1].error } : null,
          ...wv(ne),
          ...be ? { fetchers: new Map(x.fetchers) } : {}
        },
        { flushSync: oe }
      ), { shortCircuited: !0 };
    }
    if (Ye) {
      let be = {};
      if (!K) {
        be.navigation = we;
        let nt = er(ne);
        nt !== void 0 && (be.actionData = nt);
      }
      xt.length > 0 && (be.fetchers = bn(xt)), B(be, { flushSync: oe });
    }
    xt.forEach((be) => {
      ir(be.key), be.controller && F.set(be.key, be.controller);
    });
    let Wr = () => xt.forEach((be) => ir(be.key));
    A && A.signal.addEventListener(
      "abort",
      Wr
    );
    let { loaderResults: ha, fetcherResults: Sr } = await Gp(
      Te,
      xt,
      E,
      U
    );
    if (E.signal.aborted)
      return { shortCircuited: !0 };
    A && A.signal.removeEventListener(
      "abort",
      Wr
    ), xt.forEach((be) => F.delete(be.key));
    let Wt = Ki(ha);
    if (Wt)
      return await Ut(E, Wt.result, !0, {
        replace: re
      }), { shortCircuited: !0 };
    if (Wt = Ki(Sr), Wt)
      return G.add(Wt.key), await Ut(E, Wt.result, !0, {
        replace: re
      }), { shortCircuited: !0 };
    let { loaderData: Yu, errors: pa } = bv(
      x,
      I,
      ha,
      ne,
      xt,
      Sr
    );
    ae && x.errors && (pa = { ...x.errors, ...pa });
    let Hr = Xp(), zi = Yp(X), Ui = Hr || zi || xt.length > 0;
    return {
      matches: I,
      loaderData: Yu,
      errors: pa,
      ...Ui ? { fetchers: new Map(x.fetchers) } : {}
    };
  }
  function er(E) {
    if (E && !yt(E[1]))
      return {
        [E[0]]: E[1].data
      };
    if (x.actionData)
      return Object.keys(x.actionData).length === 0 ? null : x.actionData;
  }
  function bn(E) {
    return E.forEach((j) => {
      let I = x.fetchers.get(j.key), U = ma(
        void 0,
        I ? I.data : void 0
      );
      x.fetchers.set(j.key, U);
    }), new Map(x.fetchers);
  }
  async function tr(E, j, I, U) {
    ir(E);
    let K = (U && U.flushSync) === !0, te = l || u, Z = ad(
      x.location,
      x.matches,
      s,
      I,
      j,
      U?.relative
    ), ee = Er(te, Z, s), re = Bi(ee, te, Z);
    if (re.active && re.matches && (ee = re.matches), !ee) {
      ar(
        E,
        j,
        St(404, { pathname: Z }),
        { flushSync: K }
      );
      return;
    }
    let { path: ae, submission: oe, error: ne } = fv(
      !0,
      Z,
      U
    );
    if (ne) {
      ar(E, j, ne, { flushSync: K });
      return;
    }
    let xe = e.getContext ? await e.getContext() : new iv(), we = (U && U.preventScrollReset) === !0;
    if (oe && it(oe.formMethod)) {
      await rr(
        E,
        j,
        ae,
        ee,
        xe,
        re.active,
        K,
        we,
        oe,
        U && U.unstable_defaultShouldRevalidate
      );
      return;
    }
    Q.set(E, { routeId: j, path: ae }), await da(
      E,
      j,
      ae,
      ee,
      xe,
      re.active,
      K,
      we,
      oe
    );
  }
  async function rr(E, j, I, U, K, te, Z, ee, re, ae) {
    Ku(), Q.delete(E);
    let oe = x.fetchers.get(E);
    nr(E, QP(re, oe), {
      flushSync: Z
    });
    let ne = new AbortController(), xe = Pn(
      e.history,
      I,
      ne.signal,
      re
    );
    if (te) {
      let De = await Fi(
        U,
        new URL(xe.url).pathname,
        xe.signal,
        E
      );
      if (De.type === "aborted")
        return;
      if (De.type === "error") {
        ar(E, j, De.error, { flushSync: Z });
        return;
      } else if (De.matches)
        U = De.matches;
      else {
        ar(
          E,
          j,
          St(404, { pathname: I }),
          { flushSync: Z }
        );
        return;
      }
    }
    let we = so(U, I);
    if (!we.route.action && !we.route.lazy) {
      let De = St(405, {
        method: re.formMethod,
        pathname: I,
        routeId: j
      });
      ar(E, j, De, { flushSync: Z });
      return;
    }
    F.set(E, ne);
    let Qe = W, Ye = jn(
      i,
      o,
      xe,
      U,
      we,
      n,
      K
    ), lt = await _r(
      xe,
      Ye,
      K,
      E
    ), Te = lt[we.route.id];
    if (!Te) {
      for (let De of Ye)
        if (lt[De.route.id]) {
          Te = lt[De.route.id];
          break;
        }
    }
    if (xe.signal.aborted) {
      F.get(E) === ne && F.delete(E);
      return;
    }
    if (ge.has(E)) {
      if (Yr(Te) || yt(Te)) {
        nr(E, ur(void 0));
        return;
      }
    } else {
      if (Yr(Te))
        if (F.delete(E), X > Qe) {
          nr(E, ur(void 0));
          return;
        } else
          return G.add(E), nr(E, ma(re)), Ut(xe, Te, !1, {
            fetcherSubmission: re,
            preventScrollReset: ee
          });
      if (yt(Te)) {
        ar(E, j, Te.error);
        return;
      }
    }
    let xt = x.navigation.location || x.location, Wr = Pn(
      e.history,
      xt,
      ne.signal
    ), ha = l || u, Sr = x.navigation.state !== "idle" ? Er(ha, x.navigation.location, s) : x.matches;
    he(Sr, "Didn't find any matches after fetcher action");
    let Wt = ++W;
    J.set(E, Wt);
    let Yu = ma(re, Te.data);
    x.fetchers.set(E, Yu);
    let { dsMatches: pa, revalidatingFetchers: Hr } = dv(
      Wr,
      K,
      i,
      o,
      e.history,
      x,
      Sr,
      re,
      xt,
      n,
      !1,
      L,
      z,
      ge,
      Q,
      G,
      ha,
      s,
      e.patchRoutesOnNavigation != null,
      [we.route.id, Te],
      ae
    );
    Hr.filter((De) => De.key !== E).forEach((De) => {
      let Wi = De.key, rv = x.fetchers.get(Wi), AS = ma(
        void 0,
        rv ? rv.data : void 0
      );
      x.fetchers.set(Wi, AS), ir(Wi), De.controller && F.set(Wi, De.controller);
    }), B({ fetchers: new Map(x.fetchers) });
    let zi = () => Hr.forEach((De) => ir(De.key));
    ne.signal.addEventListener(
      "abort",
      zi
    );
    let { loaderResults: Ui, fetcherResults: be } = await Gp(
      pa,
      Hr,
      Wr,
      K
    );
    if (ne.signal.aborted)
      return;
    if (ne.signal.removeEventListener(
      "abort",
      zi
    ), J.delete(E), F.delete(E), Hr.forEach((De) => F.delete(De.key)), x.fetchers.has(E)) {
      let De = ur(Te.data);
      x.fetchers.set(E, De);
    }
    let nt = Ki(Ui);
    if (nt)
      return Ut(
        Wr,
        nt.result,
        !1,
        { preventScrollReset: ee }
      );
    if (nt = Ki(be), nt)
      return G.add(nt.key), Ut(
        Wr,
        nt.result,
        !1,
        { preventScrollReset: ee }
      );
    let { loaderData: xn, errors: Gr } = bv(
      x,
      Sr,
      Ui,
      void 0,
      Hr,
      be
    );
    Yp(Wt), x.navigation.state === "loading" && Wt > X ? (he(S, "Expected pending action"), A && A.abort(), Ee(x.navigation.location, {
      matches: Sr,
      loaderData: xn,
      errors: Gr,
      fetchers: new Map(x.fetchers)
    })) : (B({
      errors: Gr,
      loaderData: xv(
        x.loaderData,
        xn,
        Sr,
        Gr
      ),
      fetchers: new Map(x.fetchers)
    }), L = !1);
  }
  async function da(E, j, I, U, K, te, Z, ee, re) {
    let ae = x.fetchers.get(E);
    nr(
      E,
      ma(
        re,
        ae ? ae.data : void 0
      ),
      { flushSync: Z }
    );
    let oe = new AbortController(), ne = Pn(
      e.history,
      I,
      oe.signal
    );
    if (te) {
      let Te = await Fi(
        U,
        new URL(ne.url).pathname,
        ne.signal,
        E
      );
      if (Te.type === "aborted")
        return;
      if (Te.type === "error") {
        ar(E, j, Te.error, { flushSync: Z });
        return;
      } else if (Te.matches)
        U = Te.matches;
      else {
        ar(
          E,
          j,
          St(404, { pathname: I }),
          { flushSync: Z }
        );
        return;
      }
    }
    let xe = so(U, I);
    F.set(E, oe);
    let we = W, Qe = jn(
      i,
      o,
      ne,
      U,
      xe,
      n,
      K
    ), lt = (await _r(
      ne,
      Qe,
      K,
      E
    ))[xe.route.id];
    if (F.get(E) === oe && F.delete(E), !ne.signal.aborted) {
      if (ge.has(E)) {
        nr(E, ur(void 0));
        return;
      }
      if (Yr(lt))
        if (X > we) {
          nr(E, ur(void 0));
          return;
        } else {
          G.add(E), await Ut(ne, lt, !1, {
            preventScrollReset: ee
          });
          return;
        }
      if (yt(lt)) {
        ar(E, j, lt.error);
        return;
      }
      nr(E, ur(lt.data));
    }
  }
  async function Ut(E, j, I, {
    submission: U,
    fetcherSubmission: K,
    preventScrollReset: te,
    replace: Z
  } = {}) {
    I || (T?.resolve(), T = null), j.response.headers.has("X-Remix-Revalidate") && (L = !0);
    let ee = j.response.headers.get("Location");
    he(ee, "Expected a Location header on the redirect Response"), ee = mv(
      ee,
      new URL(E.url),
      s,
      e.history
    );
    let re = Fa(x.location, ee, {
      _isRedirect: !0
    });
    if (r) {
      let Qe = !1;
      if (j.response.headers.has("X-Remix-Reload-Document"))
        Qe = !0;
      else if (Eh(ee)) {
        const Ye = P1(ee, !0);
        Qe = // Hard reload if it's an absolute URL to a new origin
        Ye.origin !== t.location.origin || // Hard reload if it's an absolute URL that does not match our basename
        Mt(Ye.pathname, s) == null;
      }
      if (Qe) {
        Z ? t.location.replace(ee) : t.location.assign(ee);
        return;
      }
    }
    A = null;
    let ae = Z === !0 || j.response.headers.has("X-Remix-Replace") ? "REPLACE" : "PUSH", { formMethod: oe, formAction: ne, formEncType: xe } = x.navigation;
    !U && !K && oe && ne && xe && (U = _v(x.navigation));
    let we = U || K;
    if (TP.has(j.response.status) && we && it(we.formMethod))
      await je(ae, re, {
        submission: {
          ...we,
          formAction: ee
        },
        // Preserve these flags across redirects
        preventScrollReset: te || C,
        enableViewTransition: I ? N : void 0
      });
    else {
      let Qe = el(
        re,
        U
      );
      await je(ae, re, {
        overrideNavigation: Qe,
        // Send fetcher submissions through for shouldRevalidate
        fetcherSubmission: K,
        // Preserve these flags across redirects
        preventScrollReset: te || C,
        enableViewTransition: I ? N : void 0
      });
    }
  }
  async function _r(E, j, I, U) {
    let K, te = {};
    try {
      K = await LP(
        f,
        E,
        j,
        U,
        I,
        !1
      );
    } catch (Z) {
      return j.filter((ee) => ee.shouldLoad).forEach((ee) => {
        te[ee.route.id] = {
          type: "error",
          error: Z
        };
      }), te;
    }
    if (E.signal.aborted)
      return te;
    if (!it(E.method))
      for (let Z of j) {
        if (K[Z.route.id]?.type === "error")
          break;
        !K.hasOwnProperty(Z.route.id) && !x.loaderData.hasOwnProperty(Z.route.id) && (!x.errors || !x.errors.hasOwnProperty(Z.route.id)) && Z.shouldCallHandler() && (K[Z.route.id] = {
          type: "error",
          result: new Error(
            `No result returned from dataStrategy for route ${Z.route.id}`
          )
        });
      }
    for (let [Z, ee] of Object.entries(K))
      if (VP(ee)) {
        let re = ee.result;
        te[Z] = {
          type: "redirect",
          response: zP(
            re,
            E,
            Z,
            j,
            s
          )
        };
      } else
        te[Z] = await FP(ee);
    return te;
  }
  async function Gp(E, j, I, U) {
    let K = _r(
      I,
      E,
      U,
      null
    ), te = Promise.all(
      j.map(async (re) => {
        if (re.matches && re.match && re.request && re.controller) {
          let oe = (await _r(
            re.request,
            re.matches,
            U,
            re.key
          ))[re.match.route.id];
          return { [re.key]: oe };
        } else
          return Promise.resolve({
            [re.key]: {
              type: "error",
              error: St(404, {
                pathname: re.path
              })
            }
          });
      })
    ), Z = await K, ee = (await te).reduce(
      (re, ae) => Object.assign(re, ae),
      {}
    );
    return {
      loaderResults: Z,
      fetcherResults: ee
    };
  }
  function Ku() {
    L = !0, Q.forEach((E, j) => {
      F.has(j) && z.add(j), ir(j);
    });
  }
  function nr(E, j, I = {}) {
    x.fetchers.set(E, j), B(
      { fetchers: new Map(x.fetchers) },
      { flushSync: (I && I.flushSync) === !0 }
    );
  }
  function ar(E, j, I, U = {}) {
    let K = Tr(x.matches, j);
    Vu(E), B(
      {
        errors: {
          [K.route.id]: I
        },
        fetchers: new Map(x.fetchers)
      },
      { flushSync: (U && U.flushSync) === !0 }
    );
  }
  function Kp(E) {
    return de.set(E, (de.get(E) || 0) + 1), ge.has(E) && ge.delete(E), x.fetchers.get(E) || MP;
  }
  function bS(E, j) {
    ir(E, j?.reason), nr(E, ur(null));
  }
  function Vu(E) {
    let j = x.fetchers.get(E);
    F.has(E) && !(j && j.state === "loading" && J.has(E)) && ir(E), Q.delete(E), J.delete(E), G.delete(E), ge.delete(E), z.delete(E), x.fetchers.delete(E);
  }
  function xS(E) {
    let j = (de.get(E) || 0) - 1;
    j <= 0 ? (de.delete(E), ge.add(E)) : de.set(E, j), B({ fetchers: new Map(x.fetchers) });
  }
  function ir(E, j) {
    let I = F.get(E);
    I && (I.abort(j), F.delete(E));
  }
  function Vp(E) {
    for (let j of E) {
      let I = Kp(j), U = ur(I.data);
      x.fetchers.set(j, U);
    }
  }
  function Xp() {
    let E = [], j = !1;
    for (let I of G) {
      let U = x.fetchers.get(I);
      he(U, `Expected fetcher: ${I}`), U.state === "loading" && (G.delete(I), E.push(I), j = !0);
    }
    return Vp(E), j;
  }
  function Yp(E) {
    let j = [];
    for (let [I, U] of J)
      if (U < E) {
        let K = x.fetchers.get(I);
        he(K, `Expected fetcher: ${I}`), K.state === "loading" && (ir(I), J.delete(I), j.push(I));
      }
    return Vp(j), j.length > 0;
  }
  function wS(E, j) {
    let I = x.blockers.get(E) || ya;
    return qe.get(E) !== j && qe.set(E, j), I;
  }
  function Zp(E) {
    x.blockers.delete(E), qe.delete(E);
  }
  function qi(E, j) {
    let I = x.blockers.get(E) || ya;
    he(
      I.state === "unblocked" && j.state === "blocked" || I.state === "blocked" && j.state === "blocked" || I.state === "blocked" && j.state === "proceeding" || I.state === "blocked" && j.state === "unblocked" || I.state === "proceeding" && j.state === "unblocked",
      `Invalid blocker state transition: ${I.state} -> ${j.state}`
    );
    let U = new Map(x.blockers);
    U.set(E, j), B({ blockers: U });
  }
  function Jp({
    currentLocation: E,
    nextLocation: j,
    historyAction: I
  }) {
    if (qe.size === 0)
      return;
    qe.size > 1 && We(!1, "A router only supports one blocker at a time");
    let U = Array.from(qe.entries()), [K, te] = U[U.length - 1], Z = x.blockers.get(K);
    if (!(Z && Z.state === "proceeding") && te({ currentLocation: E, nextLocation: j, historyAction: I }))
      return K;
  }
  function Xu(E) {
    let j = St(404, { pathname: E }), I = l || u, { matches: U, route: K } = Gi(I);
    return { notFoundMatches: U, route: K, error: j };
  }
  function OS(E, j, I) {
    if (y = E, p = j, v = I || null, !g && x.navigation === Qu) {
      g = !0;
      let U = ev(x.location, x.matches);
      U != null && B({ restoreScrollPosition: U });
    }
    return () => {
      y = null, p = null, v = null;
    };
  }
  function Qp(E, j) {
    return v && v(
      E,
      j.map((U) => tP(U, x.loaderData))
    ) || E.key;
  }
  function _S(E, j) {
    if (y && p) {
      let I = Qp(E, j);
      y[I] = p();
    }
  }
  function ev(E, j) {
    if (y) {
      let I = Qp(E, j), U = y[I];
      if (typeof U == "number")
        return U;
    }
    return null;
  }
  function Bi(E, j, I) {
    if (e.patchRoutesOnNavigation)
      if (E) {
        if (Object.keys(E[0].params).length > 0)
          return { active: !0, matches: ja(
            j,
            I,
            s,
            !0
          ) };
      } else
        return { active: !0, matches: ja(
          j,
          I,
          s,
          !0
        ) || [] };
    return { active: !1, matches: null };
  }
  async function Fi(E, j, I, U) {
    if (!e.patchRoutesOnNavigation)
      return { type: "success", matches: E };
    let K = E;
    for (; ; ) {
      let te = l == null, Z = l || u, ee = o;
      try {
        await e.patchRoutesOnNavigation({
          signal: I,
          path: j,
          matches: K,
          fetcherKey: U,
          patch: (oe, ne) => {
            I.aborted || hv(
              oe,
              ne,
              Z,
              ee,
              i,
              !1
            );
          }
        });
      } catch (oe) {
        return { type: "error", error: oe, partialMatches: K };
      } finally {
        te && !I.aborted && (u = [...u]);
      }
      if (I.aborted)
        return { type: "aborted" };
      let re = Er(Z, j, s), ae = null;
      if (re) {
        if (Object.keys(re[0].params).length === 0)
          return { type: "success", matches: re };
        if (ae = ja(
          Z,
          j,
          s,
          !0
        ), !(ae && K.length < ae.length && tv(
          K,
          ae.slice(0, K.length)
        )))
          return { type: "success", matches: re };
      }
      if (ae || (ae = ja(
        Z,
        j,
        s,
        !0
      )), !ae || tv(K, ae))
        return { type: "success", matches: null };
      K = ae;
    }
  }
  function tv(E, j) {
    return E.length === j.length && E.every((I, U) => I.route.id === j[U].route.id);
  }
  function SS(E) {
    o = {}, l = za(
      E,
      i,
      void 0,
      o
    );
  }
  function PS(E, j, I = !1) {
    let U = l == null;
    hv(
      E,
      j,
      l || u,
      o,
      i,
      I
    ), U && (u = [...u], B({}));
  }
  return O = {
    get basename() {
      return s;
    },
    get future() {
      return c;
    },
    get state() {
      return x;
    },
    get routes() {
      return u;
    },
    get window() {
      return t;
    },
    initialize: V,
    subscribe: ce,
    enableScrollRestoration: OS,
    navigate: ye,
    fetch: tr,
    revalidate: Be,
    // Passthrough to history-aware createHref used by useHref so we get proper
    // hash-aware URLs in DOM paths
    createHref: (E) => e.history.createHref(E),
    encodeLocation: (E) => e.history.encodeLocation(E),
    getFetcher: Kp,
    resetFetcher: bS,
    deleteFetcher: xS,
    dispose: le,
    getBlocker: wS,
    deleteBlocker: Zp,
    patchRoutes: PS,
    _internalFetchControllers: F,
    // TODO: Remove setRoutes, it's temporary to avoid dealing with
    // updating the tree while validating the update algorithm.
    _internalSetRoutes: SS,
    _internalSetStateDoNotUseOrYouWillBreakYourApp(E) {
      B(E);
    }
  }, e.unstable_instrumentations && (O = xP(
    O,
    e.unstable_instrumentations.map((E) => E.router).filter(Boolean)
  )), O;
}
function CP(e) {
  return e != null && ("formData" in e && e.formData != null || "body" in e && e.body !== void 0);
}
function ad(e, t, r, n, a, i) {
  let o, u;
  if (a) {
    o = [];
    for (let s of t)
      if (o.push(s), s.route.id === a) {
        u = s;
        break;
      }
  } else
    o = t, u = t[t.length - 1];
  let l = Mh(
    n || ".",
    Th(o),
    Mt(e.pathname, r) || e.pathname,
    i === "path"
  );
  if (n == null && (l.search = e.search, l.hash = e.hash), (n == null || n === "" || n === ".") && u) {
    let s = Ch(l.search);
    if (u.route.index && !s)
      l.search = l.search ? l.search.replace(/^\?/, "?index&") : "?index";
    else if (!u.route.index && s) {
      let f = new URLSearchParams(l.search), c = f.getAll("index");
      f.delete("index"), c.filter((h) => h).forEach((h) => f.append("index", h));
      let d = f.toString();
      l.search = d ? `?${d}` : "";
    }
  }
  return r !== "/" && (l.pathname = pP({ basename: r, pathname: l.pathname })), Yt(l);
}
function fv(e, t, r) {
  if (!r || !CP(r))
    return { path: t };
  if (r.formMethod && !ZP(r.formMethod))
    return {
      path: t,
      error: St(405, { method: r.formMethod })
    };
  let n = () => ({
    path: t,
    error: St(400, { type: "invalid-body" })
  }), i = (r.formMethod || "get").toUpperCase(), o = z1(t);
  if (r.body !== void 0) {
    if (r.formEncType === "text/plain") {
      if (!it(i))
        return n();
      let c = typeof r.body == "string" ? r.body : r.body instanceof FormData || r.body instanceof URLSearchParams ? (
        // https://html.spec.whatwg.org/multipage/form-control-infrastructure.html#plain-text-form-data
        Array.from(r.body.entries()).reduce(
          (d, [h, y]) => `${d}${h}=${y}
`,
          ""
        )
      ) : String(r.body);
      return {
        path: t,
        submission: {
          formMethod: i,
          formAction: o,
          formEncType: r.formEncType,
          formData: void 0,
          json: void 0,
          text: c
        }
      };
    } else if (r.formEncType === "application/json") {
      if (!it(i))
        return n();
      try {
        let c = typeof r.body == "string" ? JSON.parse(r.body) : r.body;
        return {
          path: t,
          submission: {
            formMethod: i,
            formAction: o,
            formEncType: r.formEncType,
            formData: void 0,
            json: c,
            text: void 0
          }
        };
      } catch {
        return n();
      }
    }
  }
  he(
    typeof FormData == "function",
    "FormData is not available in this environment"
  );
  let u, l;
  if (r.formData)
    u = ud(r.formData), l = r.formData;
  else if (r.body instanceof FormData)
    u = ud(r.body), l = r.body;
  else if (r.body instanceof URLSearchParams)
    u = r.body, l = gv(u);
  else if (r.body == null)
    u = new URLSearchParams(), l = new FormData();
  else
    try {
      u = new URLSearchParams(r.body), l = gv(u);
    } catch {
      return n();
    }
  let s = {
    formMethod: i,
    formAction: o,
    formEncType: r && r.formEncType || "application/x-www-form-urlencoded",
    formData: l,
    json: void 0,
    text: void 0
  };
  if (it(s.formMethod))
    return { path: t, submission: s };
  let f = Dr(t);
  return e && f.search && Ch(f.search) && u.append("index", ""), f.search = `?${u}`, { path: Yt(f), submission: s };
}
function dv(e, t, r, n, a, i, o, u, l, s, f, c, d, h, y, v, p, g, b, w, _) {
  let m = w ? yt(w[1]) ? w[1].error : w[1].data : void 0, O = a.createURL(i.location), x = a.createURL(l), S;
  if (f && i.errors) {
    let R = Object.keys(i.errors)[0];
    S = o.findIndex((L) => L.route.id === R);
  } else if (w && yt(w[1])) {
    let R = w[0];
    S = o.findIndex((L) => L.route.id === R) - 1;
  }
  let T = w ? w[1].statusCode : void 0, C = T && T >= 400, A = {
    currentUrl: O,
    currentParams: i.matches[0]?.params || {},
    nextUrl: x,
    nextParams: o[0].params,
    ...u,
    actionResult: m,
    actionStatus: T
  }, N = Ei(o), $ = o.map((R, L) => {
    let { route: z } = R, F = null;
    if (S != null && L > S ? F = !1 : z.lazy ? F = !0 : jh(z) ? f ? F = id(
      z,
      i.loaderData,
      i.errors
    ) : $P(i.loaderData, i.matches[L], R) && (F = !0) : F = !1, F !== null)
      return od(
        r,
        n,
        e,
        N,
        R,
        s,
        t,
        F
      );
    let W = !1;
    typeof _ == "boolean" ? W = _ : C ? W = !1 : (c || O.pathname + O.search === x.pathname + x.search || O.search !== x.search || RP(i.matches[L], R)) && (W = !0);
    let X = {
      ...A,
      defaultShouldRevalidate: W
    }, J = Ra(R, X);
    return od(
      r,
      n,
      e,
      N,
      R,
      s,
      t,
      J,
      X,
      _
    );
  }), D = [];
  return y.forEach((R, L) => {
    if (f || !o.some((de) => de.route.id === R.routeId) || h.has(L))
      return;
    let z = i.fetchers.get(L), F = z && z.state !== "idle" && z.data === void 0, W = Er(p, R.path, g);
    if (!W) {
      if (b && F)
        return;
      D.push({
        key: L,
        routeId: R.routeId,
        path: R.path,
        matches: null,
        match: null,
        request: null,
        controller: null
      });
      return;
    }
    if (v.has(L))
      return;
    let X = so(W, R.path), J = new AbortController(), G = Pn(
      a,
      R.path,
      J.signal
    ), Q = null;
    if (d.has(L))
      d.delete(L), Q = jn(
        r,
        n,
        G,
        W,
        X,
        s,
        t
      );
    else if (F)
      c && (Q = jn(
        r,
        n,
        G,
        W,
        X,
        s,
        t
      ));
    else {
      let de;
      typeof _ == "boolean" ? de = _ : C ? de = !1 : de = c;
      let ge = {
        ...A,
        defaultShouldRevalidate: de
      };
      Ra(X, ge) && (Q = jn(
        r,
        n,
        G,
        W,
        X,
        s,
        t,
        ge
      ));
    }
    Q && D.push({
      key: L,
      routeId: R.routeId,
      path: R.path,
      matches: Q,
      match: X,
      request: G,
      controller: J
    });
  }), { dsMatches: $, revalidatingFetchers: D };
}
function jh(e) {
  return e.loader != null || e.middleware != null && e.middleware.length > 0;
}
function id(e, t, r) {
  if (e.lazy)
    return !0;
  if (!jh(e))
    return !1;
  let n = t != null && e.id in t, a = r != null && r[e.id] !== void 0;
  return !n && a ? !1 : typeof e.loader == "function" && e.loader.hydrate === !0 ? !0 : !n && !a;
}
function $P(e, t, r) {
  let n = (
    // [a] -> [a, b]
    !t || // [a, b] -> [a, c]
    r.route.id !== t.route.id
  ), a = !e.hasOwnProperty(r.route.id);
  return n || a;
}
function RP(e, t) {
  let r = e.route.path;
  return (
    // param change for this match, /users/123 -> /users/456
    e.pathname !== t.pathname || // splat param changed, which is not present in match.path
    // e.g. /files/images/avatar.jpg -> files/finances.xls
    r != null && r.endsWith("*") && e.params["*"] !== t.params["*"]
  );
}
function Ra(e, t) {
  if (e.route.shouldRevalidate) {
    let r = e.route.shouldRevalidate(t);
    if (typeof r == "boolean")
      return r;
  }
  return t.defaultShouldRevalidate;
}
function hv(e, t, r, n, a, i) {
  let o;
  if (e) {
    let s = n[e];
    he(
      s,
      `No route found to patch children into: routeId = ${e}`
    ), s.children || (s.children = []), o = s.children;
  } else
    o = r;
  let u = [], l = [];
  if (t.forEach((s) => {
    let f = o.find(
      (c) => D1(s, c)
    );
    f ? l.push({ existingRoute: f, newRoute: s }) : u.push(s);
  }), u.length > 0) {
    let s = za(
      u,
      a,
      [e || "_", "patch", String(o?.length || "0")],
      n
    );
    o.push(...s);
  }
  if (i && l.length > 0)
    for (let s = 0; s < l.length; s++) {
      let { existingRoute: f, newRoute: c } = l[s], d = f, [h] = za(
        [c],
        a,
        [],
        // Doesn't matter for mutated routes since they already have an id
        {},
        // Don't touch the manifest here since we're updating in place
        !0
      );
      Object.assign(d, {
        element: h.element ? h.element : d.element,
        errorElement: h.errorElement ? h.errorElement : d.errorElement,
        hydrateFallbackElement: h.hydrateFallbackElement ? h.hydrateFallbackElement : d.hydrateFallbackElement
      });
    }
}
function D1(e, t) {
  return "id" in e && "id" in t && e.id === t.id ? !0 : e.index === t.index && e.path === t.path && e.caseSensitive === t.caseSensitive ? (!e.children || e.children.length === 0) && (!t.children || t.children.length === 0) ? !0 : e.children.every(
    (r, n) => t.children?.some((a) => D1(r, a))
  ) : !1;
}
var pv = /* @__PURE__ */ new WeakMap(), L1 = ({
  key: e,
  route: t,
  manifest: r,
  mapRouteProperties: n
}) => {
  let a = r[t.id];
  if (he(a, "No route found in manifest"), !a.lazy || typeof a.lazy != "object")
    return;
  let i = a.lazy[e];
  if (!i)
    return;
  let o = pv.get(a);
  o || (o = {}, pv.set(a, o));
  let u = o[e];
  if (u)
    return u;
  let l = (async () => {
    let s = ZS(e), c = a[e] !== void 0 && e !== "hasErrorBoundary";
    if (s)
      We(
        !s,
        "Route property " + e + " is not a supported lazy route property. This property will be ignored."
      ), o[e] = Promise.resolve();
    else if (c)
      We(
        !1,
        `Route "${a.id}" has a static property "${e}" defined. The lazy property will be ignored.`
      );
    else {
      let d = await i();
      d != null && (Object.assign(a, { [e]: d }), Object.assign(a, n(a)));
    }
    typeof a.lazy == "object" && (a.lazy[e] = void 0, Object.values(a.lazy).every((d) => d === void 0) && (a.lazy = void 0));
  })();
  return o[e] = l, l;
}, vv = /* @__PURE__ */ new WeakMap();
function kP(e, t, r, n, a) {
  let i = r[e.id];
  if (he(i, "No route found in manifest"), !e.lazy)
    return {
      lazyRoutePromise: void 0,
      lazyHandlerPromise: void 0
    };
  if (typeof e.lazy == "function") {
    let f = vv.get(i);
    if (f)
      return {
        lazyRoutePromise: f,
        lazyHandlerPromise: f
      };
    let c = (async () => {
      he(
        typeof e.lazy == "function",
        "No lazy route function found"
      );
      let d = await e.lazy(), h = {};
      for (let y in d) {
        let v = d[y];
        if (v === void 0)
          continue;
        let p = QS(y), b = i[y] !== void 0 && // This property isn't static since it should always be updated based
        // on the route updates
        y !== "hasErrorBoundary";
        p ? We(
          !p,
          "Route property " + y + " is not a supported property to be returned from a lazy route function. This property will be ignored."
        ) : b ? We(
          !b,
          `Route "${i.id}" has a static property "${y}" defined but its lazy function is also returning a value for this property. The lazy route property "${y}" will be ignored.`
        ) : h[y] = v;
      }
      Object.assign(i, h), Object.assign(i, {
        // To keep things framework agnostic, we use the provided `mapRouteProperties`
        // function to set the framework-aware properties (`element`/`hasErrorBoundary`)
        // since the logic will differ between frameworks.
        ...n(i),
        lazy: void 0
      });
    })();
    return vv.set(i, c), c.catch(() => {
    }), {
      lazyRoutePromise: c,
      lazyHandlerPromise: c
    };
  }
  let o = Object.keys(e.lazy), u = [], l;
  for (let f of o) {
    if (a && a.includes(f))
      continue;
    let c = L1({
      key: f,
      route: e,
      manifest: r,
      mapRouteProperties: n
    });
    c && (u.push(c), f === t && (l = c));
  }
  let s = u.length > 0 ? Promise.all(u).then(() => {
  }) : void 0;
  return s?.catch(() => {
  }), l?.catch(() => {
  }), {
    lazyRoutePromise: s,
    lazyHandlerPromise: l
  };
}
async function yv(e) {
  let t = e.matches.filter((a) => a.shouldLoad), r = {};
  return (await Promise.all(t.map((a) => a.resolve()))).forEach((a, i) => {
    r[t[i].route.id] = a;
  }), r;
}
async function IP(e) {
  return e.matches.some((t) => t.route.middleware) ? q1(e, () => yv(e)) : yv(e);
}
function q1(e, t) {
  return DP(
    e,
    t,
    (n) => {
      if (YP(n))
        throw n;
      return n;
    },
    GP,
    r
  );
  function r(n, a, i) {
    if (i)
      return Promise.resolve(
        Object.assign(i.value, {
          [a]: { type: "error", result: n }
        })
      );
    {
      let { matches: o } = e, u = Math.min(
        // Throwing route
        Math.max(
          o.findIndex((s) => s.route.id === a),
          0
        ),
        // or the shallowest route that needs to load data
        Math.max(
          o.findIndex((s) => s.shouldCallHandler()),
          0
        )
      ), l = Tr(
        o,
        o[u].route.id
      ).route.id;
      return Promise.resolve({
        [l]: { type: "error", result: n }
      });
    }
  }
}
async function DP(e, t, r, n, a) {
  let { matches: i, request: o, params: u, context: l, unstable_pattern: s } = e, f = i.flatMap(
    (d) => d.route.middleware ? d.route.middleware.map((h) => [d.route.id, h]) : []
  );
  return await B1(
    {
      request: o,
      params: u,
      context: l,
      unstable_pattern: s
    },
    f,
    t,
    r,
    n,
    a
  );
}
async function B1(e, t, r, n, a, i, o = 0) {
  let { request: u } = e;
  if (u.signal.aborted)
    throw u.signal.reason ?? new Error(`Request aborted: ${u.method} ${u.url}`);
  let l = t[o];
  if (!l)
    return await r();
  let [s, f] = l, c, d = async () => {
    if (c)
      throw new Error("You may only call `next()` once per middleware");
    try {
      return c = { value: await B1(
        e,
        t,
        r,
        n,
        a,
        i,
        o + 1
      ) }, c.value;
    } catch (h) {
      return c = { value: await i(h, s, c) }, c.value;
    }
  };
  try {
    let h = await f(e, d), y = h != null ? n(h) : void 0;
    return a(y) ? y : c ? y ?? c.value : (c = { value: await d() }, c.value);
  } catch (h) {
    return await i(h, s, c);
  }
}
function F1(e, t, r, n, a) {
  let i = L1({
    key: "middleware",
    route: n.route,
    manifest: t,
    mapRouteProperties: e
  }), o = kP(
    n.route,
    it(r.method) ? "action" : "loader",
    t,
    e,
    a
  );
  return {
    middleware: i,
    route: o.lazyRoutePromise,
    handler: o.lazyHandlerPromise
  };
}
function od(e, t, r, n, a, i, o, u, l = null, s) {
  let f = !1, c = F1(
    e,
    t,
    r,
    a,
    i
  );
  return {
    ...a,
    _lazyPromises: c,
    shouldLoad: u,
    shouldRevalidateArgs: l,
    shouldCallHandler(d) {
      return f = !0, l ? typeof s == "boolean" ? Ra(a, {
        ...l,
        defaultShouldRevalidate: s
      }) : typeof d == "boolean" ? Ra(a, {
        ...l,
        defaultShouldRevalidate: d
      }) : Ra(a, l) : u;
    },
    resolve(d) {
      let { lazy: h, loader: y, middleware: v } = a.route, p = f || u || d && !it(r.method) && (h || y), g = v && v.length > 0 && !y && !h;
      return p && (it(r.method) || !g) ? qP({
        request: r,
        unstable_pattern: n,
        match: a,
        lazyHandlerPromise: c?.handler,
        lazyRoutePromise: c?.route,
        handlerOverride: d,
        scopedContext: o
      }) : Promise.resolve({ type: "data", result: void 0 });
    }
  };
}
function jn(e, t, r, n, a, i, o, u = null) {
  return n.map((l) => l.route.id !== a.route.id ? {
    ...l,
    shouldLoad: !1,
    shouldRevalidateArgs: u,
    shouldCallHandler: () => !1,
    _lazyPromises: F1(
      e,
      t,
      r,
      l,
      i
    ),
    resolve: () => Promise.resolve({ type: "data", result: void 0 })
  } : od(
    e,
    t,
    r,
    Ei(n),
    l,
    i,
    o,
    !0,
    u
  ));
}
async function LP(e, t, r, n, a, i) {
  r.some((s) => s._lazyPromises?.middleware) && await Promise.all(r.map((s) => s._lazyPromises?.middleware));
  let o = {
    request: t,
    unstable_pattern: Ei(r),
    params: r[0].params,
    context: a,
    matches: r
  }, l = await e({
    ...o,
    fetcherKey: n,
    runClientMiddleware: (s) => {
      let f = o;
      return q1(f, () => s({
        ...f,
        fetcherKey: n,
        runClientMiddleware: () => {
          throw new Error(
            "Cannot call `runClientMiddleware()` from within an `runClientMiddleware` handler"
          );
        }
      }));
    }
  });
  try {
    await Promise.all(
      r.flatMap((s) => [
        s._lazyPromises?.handler,
        s._lazyPromises?.route
      ])
    );
  } catch {
  }
  return l;
}
async function qP({
  request: e,
  unstable_pattern: t,
  match: r,
  lazyHandlerPromise: n,
  lazyRoutePromise: a,
  handlerOverride: i,
  scopedContext: o
}) {
  let u, l, s = it(e.method), f = s ? "action" : "loader", c = (d) => {
    let h, y = new Promise((g, b) => h = b);
    l = () => h(), e.signal.addEventListener("abort", l);
    let v = (g) => typeof d != "function" ? Promise.reject(
      new Error(
        `You cannot call the handler for a route which defines a boolean "${f}" [routeId: ${r.route.id}]`
      )
    ) : d(
      {
        request: e,
        unstable_pattern: t,
        params: r.params,
        context: o
      },
      ...g !== void 0 ? [g] : []
    ), p = (async () => {
      try {
        return { type: "data", result: await (i ? i((b) => v(b)) : v()) };
      } catch (g) {
        return { type: "error", result: g };
      }
    })();
    return Promise.race([p, y]);
  };
  try {
    let d = s ? r.route.action : r.route.loader;
    if (n || a)
      if (d) {
        let h, [y] = await Promise.all([
          // If the handler throws, don't let it immediately bubble out,
          // since we need to let the lazy() execution finish so we know if this
          // route has a boundary that can handle the error
          c(d).catch((v) => {
            h = v;
          }),
          // Ensure all lazy route promises are resolved before continuing
          n,
          a
        ]);
        if (h !== void 0)
          throw h;
        u = y;
      } else {
        await n;
        let h = s ? r.route.action : r.route.loader;
        if (h)
          [u] = await Promise.all([c(h), a]);
        else if (f === "action") {
          let y = new URL(e.url), v = y.pathname + y.search;
          throw St(405, {
            method: e.method,
            pathname: v,
            routeId: r.route.id
          });
        } else
          return { type: "data", result: void 0 };
      }
    else if (d)
      u = await c(d);
    else {
      let h = new URL(e.url), y = h.pathname + h.search;
      throw St(404, {
        pathname: y
      });
    }
  } catch (d) {
    return { type: "error", result: d };
  } finally {
    l && e.signal.removeEventListener("abort", l);
  }
  return u;
}
async function BP(e) {
  let t = e.headers.get("Content-Type");
  return t && /\bapplication\/json\b/.test(t) ? e.body == null ? null : e.json() : e.text();
}
async function FP(e) {
  let { result: t, type: r } = e;
  if (Nh(t)) {
    let n;
    try {
      n = await BP(t);
    } catch (a) {
      return { type: "error", error: a };
    }
    return r === "error" ? {
      type: "error",
      error: new Ai(t.status, t.statusText, n),
      statusCode: t.status,
      headers: t.headers
    } : {
      type: "data",
      data: n,
      statusCode: t.status,
      headers: t.headers
    };
  }
  return r === "error" ? Ov(t) ? t.data instanceof Error ? {
    type: "error",
    error: t.data,
    statusCode: t.init?.status,
    headers: t.init?.headers ? new Headers(t.init.headers) : void 0
  } : {
    type: "error",
    error: HP(t),
    statusCode: Ua(t) ? t.status : void 0,
    headers: t.init?.headers ? new Headers(t.init.headers) : void 0
  } : {
    type: "error",
    error: t,
    statusCode: Ua(t) ? t.status : void 0
  } : Ov(t) ? {
    type: "data",
    data: t.data,
    statusCode: t.init?.status,
    headers: t.init?.headers ? new Headers(t.init.headers) : void 0
  } : { type: "data", data: t };
}
function zP(e, t, r, n, a) {
  let i = e.headers.get("Location");
  if (he(
    i,
    "Redirects returned/thrown from loaders/actions must have a Location header"
  ), !Eh(i)) {
    let o = n.slice(
      0,
      n.findIndex((u) => u.route.id === r) + 1
    );
    i = ad(
      new URL(t.url),
      o,
      a,
      i
    ), e.headers.set("Location", i);
  }
  return e;
}
function mv(e, t, r, n) {
  let a = [
    "about:",
    "blob:",
    "chrome:",
    "chrome-untrusted:",
    "content:",
    "data:",
    "devtools:",
    "file:",
    "filesystem:",
    // eslint-disable-next-line no-script-url
    "javascript:"
  ];
  if (Eh(e)) {
    let i = e, o = i.startsWith("//") ? new URL(t.protocol + i) : new URL(i);
    if (a.includes(o.protocol))
      throw new Error("Invalid redirect location");
    let u = Mt(o.pathname, r) != null;
    if (o.origin === t.origin && u)
      return o.pathname + o.search + o.hash;
  }
  try {
    let i = n.createURL(e);
    if (a.includes(i.protocol))
      throw new Error("Invalid redirect location");
  } catch {
  }
  return e;
}
function Pn(e, t, r, n) {
  let a = e.createURL(z1(t)).toString(), i = { signal: r };
  if (n && it(n.formMethod)) {
    let { formMethod: o, formEncType: u } = n;
    i.method = o.toUpperCase(), u === "application/json" ? (i.headers = new Headers({ "Content-Type": u }), i.body = JSON.stringify(n.json)) : u === "text/plain" ? i.body = n.text : u === "application/x-www-form-urlencoded" && n.formData ? i.body = ud(n.formData) : i.body = n.formData;
  }
  return new Request(a, i);
}
function ud(e) {
  let t = new URLSearchParams();
  for (let [r, n] of e.entries())
    t.append(r, typeof n == "string" ? n : n.name);
  return t;
}
function gv(e) {
  let t = new FormData();
  for (let [r, n] of e.entries())
    t.append(r, n);
  return t;
}
function UP(e, t, r, n = !1, a = !1) {
  let i = {}, o = null, u, l = !1, s = {}, f = r && yt(r[1]) ? r[1].error : void 0;
  return e.forEach((c) => {
    if (!(c.route.id in t))
      return;
    let d = c.route.id, h = t[d];
    if (he(
      !Yr(h),
      "Cannot handle redirect results in processLoaderData"
    ), yt(h)) {
      let y = h.error;
      if (f !== void 0 && (y = f, f = void 0), o = o || {}, a)
        o[d] = y;
      else {
        let v = Tr(e, d);
        o[v.route.id] == null && (o[v.route.id] = y);
      }
      n || (i[d] = I1), l || (l = !0, u = Ua(h.error) ? h.error.status : 500), h.headers && (s[d] = h.headers);
    } else
      i[d] = h.data, h.statusCode && h.statusCode !== 200 && !l && (u = h.statusCode), h.headers && (s[d] = h.headers);
  }), f !== void 0 && r && (o = { [r[0]]: f }, r[2] && (i[r[2]] = void 0)), {
    loaderData: i,
    errors: o,
    statusCode: u || 200,
    loaderHeaders: s
  };
}
function bv(e, t, r, n, a, i) {
  let { loaderData: o, errors: u } = UP(
    t,
    r,
    n
  );
  return a.filter((l) => !l.matches || l.matches.some((s) => s.shouldLoad)).forEach((l) => {
    let { key: s, match: f, controller: c } = l;
    if (c && c.signal.aborted)
      return;
    let d = i[s];
    if (he(d, "Did not find corresponding fetcher result"), yt(d)) {
      let h = Tr(e.matches, f?.route.id);
      u && u[h.route.id] || (u = {
        ...u,
        [h.route.id]: d.error
      }), e.fetchers.delete(s);
    } else if (Yr(d))
      he(!1, "Unhandled fetcher revalidation redirect");
    else {
      let h = ur(d.data);
      e.fetchers.set(s, h);
    }
  }), { loaderData: o, errors: u };
}
function xv(e, t, r, n) {
  let a = Object.entries(t).filter(([, i]) => i !== I1).reduce((i, [o, u]) => (i[o] = u, i), {});
  for (let i of r) {
    let o = i.route.id;
    if (!t.hasOwnProperty(o) && e.hasOwnProperty(o) && i.route.loader && (a[o] = e[o]), n && n.hasOwnProperty(o))
      break;
  }
  return a;
}
function wv(e) {
  return e ? yt(e[1]) ? {
    // Clear out prior actionData on errors
    actionData: {}
  } : {
    actionData: {
      [e[0]]: e[1].data
    }
  } : {};
}
function Tr(e, t) {
  return (t ? e.slice(0, e.findIndex((n) => n.route.id === t) + 1) : [...e]).reverse().find((n) => n.route.hasErrorBoundary === !0) || e[0];
}
function Gi(e) {
  let t = e.length === 1 ? e[0] : e.find((r) => r.index || !r.path || r.path === "/") || {
    id: "__shim-error-route__"
  };
  return {
    matches: [
      {
        params: {},
        pathname: "",
        pathnameBase: "",
        route: t
      }
    ],
    route: t
  };
}
function St(e, {
  pathname: t,
  routeId: r,
  method: n,
  type: a,
  message: i
} = {}) {
  let o = "Unknown Server Error", u = "Unknown @remix-run/router error";
  return e === 400 ? (o = "Bad Request", n && t && r ? u = `You made a ${n} request to "${t}" but did not provide a \`loader\` for route "${r}", so there is no way to handle the request.` : a === "invalid-body" && (u = "Unable to encode submission body")) : e === 403 ? (o = "Forbidden", u = `Route "${r}" does not match URL "${t}"`) : e === 404 ? (o = "Not Found", u = `No route matches URL "${t}"`) : e === 405 && (o = "Method Not Allowed", n && t && r ? u = `You made a ${n.toUpperCase()} request to "${t}" but did not provide an \`action\` for route "${r}", so there is no way to handle the request.` : n && (u = `Invalid request method "${n.toUpperCase()}"`)), new Ai(
    e || 500,
    o,
    new Error(u),
    !0
  );
}
function Ki(e) {
  let t = Object.entries(e);
  for (let r = t.length - 1; r >= 0; r--) {
    let [n, a] = t[r];
    if (Yr(a))
      return { key: n, result: a };
  }
}
function z1(e) {
  let t = typeof e == "string" ? Dr(e) : e;
  return Yt({ ...t, hash: "" });
}
function WP(e, t) {
  return e.pathname !== t.pathname || e.search !== t.search ? !1 : e.hash === "" ? t.hash !== "" : e.hash === t.hash ? !0 : t.hash !== "";
}
function HP(e) {
  return new Ai(
    e.init?.status ?? 500,
    e.init?.statusText ?? "Internal Server Error",
    e.data
  );
}
function GP(e) {
  return e != null && typeof e == "object" && Object.entries(e).every(
    ([t, r]) => typeof t == "string" && KP(r)
  );
}
function KP(e) {
  return e != null && typeof e == "object" && "type" in e && "result" in e && (e.type === "data" || e.type === "error");
}
function VP(e) {
  return Nh(e.result) && R1.has(e.result.status);
}
function yt(e) {
  return e.type === "error";
}
function Yr(e) {
  return (e && e.type) === "redirect";
}
function Ov(e) {
  return typeof e == "object" && e != null && "type" in e && "data" in e && "init" in e && e.type === "DataWithResponseInit";
}
function Nh(e) {
  return e != null && typeof e.status == "number" && typeof e.statusText == "string" && typeof e.headers == "object" && typeof e.body < "u";
}
function XP(e) {
  return R1.has(e);
}
function YP(e) {
  return Nh(e) && XP(e.status) && e.headers.has("Location");
}
function ZP(e) {
  return EP.has(e.toUpperCase());
}
function it(e) {
  return PP.has(e.toUpperCase());
}
function Ch(e) {
  return new URLSearchParams(e).getAll("index").some((t) => t === "");
}
function so(e, t) {
  let r = typeof t == "string" ? Dr(t).search : t.search;
  if (e[e.length - 1].route.index && Ch(r || ""))
    return e[e.length - 1];
  let n = M1(e);
  return n[n.length - 1];
}
function _v(e) {
  let { formMethod: t, formAction: r, formEncType: n, text: a, formData: i, json: o } = e;
  if (!(!t || !r || !n)) {
    if (a != null)
      return {
        formMethod: t,
        formAction: r,
        formEncType: n,
        formData: void 0,
        json: void 0,
        text: a
      };
    if (i != null)
      return {
        formMethod: t,
        formAction: r,
        formEncType: n,
        formData: i,
        json: void 0,
        text: void 0
      };
    if (o !== void 0)
      return {
        formMethod: t,
        formAction: r,
        formEncType: n,
        formData: void 0,
        json: o,
        text: void 0
      };
  }
}
function el(e, t) {
  return t ? {
    state: "loading",
    location: e,
    formMethod: t.formMethod,
    formAction: t.formAction,
    formEncType: t.formEncType,
    formData: t.formData,
    json: t.json,
    text: t.text
  } : {
    state: "loading",
    location: e,
    formMethod: void 0,
    formAction: void 0,
    formEncType: void 0,
    formData: void 0,
    json: void 0,
    text: void 0
  };
}
function JP(e, t) {
  return {
    state: "submitting",
    location: e,
    formMethod: t.formMethod,
    formAction: t.formAction,
    formEncType: t.formEncType,
    formData: t.formData,
    json: t.json,
    text: t.text
  };
}
function ma(e, t) {
  return e ? {
    state: "loading",
    formMethod: e.formMethod,
    formAction: e.formAction,
    formEncType: e.formEncType,
    formData: e.formData,
    json: e.json,
    text: e.text,
    data: t
  } : {
    state: "loading",
    formMethod: void 0,
    formAction: void 0,
    formEncType: void 0,
    formData: void 0,
    json: void 0,
    text: void 0,
    data: t
  };
}
function QP(e, t) {
  return {
    state: "submitting",
    formMethod: e.formMethod,
    formAction: e.formAction,
    formEncType: e.formEncType,
    formData: e.formData,
    json: e.json,
    text: e.text,
    data: t ? t.data : void 0
  };
}
function ur(e) {
  return {
    state: "idle",
    formMethod: void 0,
    formAction: void 0,
    formEncType: void 0,
    formData: void 0,
    json: void 0,
    text: void 0,
    data: e
  };
}
function eA(e, t) {
  try {
    let r = e.sessionStorage.getItem(
      k1
    );
    if (r) {
      let n = JSON.parse(r);
      for (let [a, i] of Object.entries(n || {}))
        i && Array.isArray(i) && t.set(a, new Set(i || []));
    }
  } catch {
  }
}
function tA(e, t) {
  if (t.size > 0) {
    let r = {};
    for (let [n, a] of t)
      r[n] = [...a];
    try {
      e.sessionStorage.setItem(
        k1,
        JSON.stringify(r)
      );
    } catch (n) {
      We(
        !1,
        `Failed to save applied view transitions in sessionStorage (${n}).`
      );
    }
  }
}
function Sv() {
  let e, t, r = new Promise((n, a) => {
    e = async (i) => {
      n(i);
      try {
        await r;
      } catch {
      }
    }, t = async (i) => {
      a(i);
      try {
        await r;
      } catch {
      }
    };
  });
  return {
    promise: r,
    //@ts-ignore
    resolve: e,
    //@ts-ignore
    reject: t
  };
}
var hn = Ge(null);
hn.displayName = "DataRouter";
var Ti = Ge(null);
Ti.displayName = "DataRouterState";
var U1 = Ge(!1);
function rA() {
  return se(U1);
}
var $h = Ge({
  isTransitioning: !1
});
$h.displayName = "ViewTransition";
var W1 = Ge(
  /* @__PURE__ */ new Map()
);
W1.displayName = "Fetchers";
var nA = Ge(null);
nA.displayName = "Await";
var Nt = Ge(
  null
);
Nt.displayName = "Navigation";
var pu = Ge(
  null
);
pu.displayName = "Location";
var Jt = Ge({
  outlet: null,
  matches: [],
  isDataRoute: !1
});
Jt.displayName = "Route";
var Rh = Ge(null);
Rh.displayName = "RouteError";
var H1 = "REACT_ROUTER_ERROR", aA = "REDIRECT", iA = "ROUTE_ERROR_RESPONSE";
function oA(e) {
  if (e.startsWith(`${H1}:${aA}:{`))
    try {
      let t = JSON.parse(e.slice(28));
      if (typeof t == "object" && t && typeof t.status == "number" && typeof t.statusText == "string" && typeof t.location == "string" && typeof t.reloadDocument == "boolean" && typeof t.replace == "boolean")
        return t;
    } catch {
    }
}
function uA(e) {
  if (e.startsWith(
    `${H1}:${iA}:{`
  ))
    try {
      let t = JSON.parse(e.slice(40));
      if (typeof t == "object" && t && typeof t.status == "number" && typeof t.statusText == "string")
        return new Ai(
          t.status,
          t.statusText,
          t.data
        );
    } catch {
    }
}
function lA(e, { relative: t } = {}) {
  he(
    Mi(),
    // TODO: This error is probably because they somehow have 2 versions of the
    // router loaded. We can help them understand how to avoid that.
    "useHref() may be used only in the context of a <Router> component."
  );
  let { basename: r, navigator: n } = se(Nt), { hash: a, pathname: i, search: o } = ji(e, { relative: t }), u = i;
  return r !== "/" && (u = i === "/" ? r : Kt([r, i])), n.createHref({ pathname: u, search: o, hash: a });
}
function Mi() {
  return se(pu) != null;
}
function pn() {
  return he(
    Mi(),
    // TODO: This error is probably because they somehow have 2 versions of the
    // router loaded. We can help them understand how to avoid that.
    "useLocation() may be used only in the context of a <Router> component."
  ), se(pu).location;
}
var G1 = "You should call navigate() in a React.useEffect(), not when your component is first rendered.";
function K1(e) {
  se(Nt).static || Ah(e);
}
function kh() {
  let { isDataRoute: e } = se(Jt);
  return e ? _A() : sA();
}
function sA() {
  he(
    Mi(),
    // TODO: This error is probably because they somehow have 2 versions of the
    // router loaded. We can help them understand how to avoid that.
    "useNavigate() may be used only in the context of a <Router> component."
  );
  let e = se(hn), { basename: t, navigator: r } = se(Nt), { matches: n } = se(Jt), { pathname: a } = pn(), i = JSON.stringify(Th(n)), o = pr(!1);
  return K1(() => {
    o.current = !0;
  }), dn(
    (l, s = {}) => {
      if (We(o.current, G1), !o.current) return;
      if (typeof l == "number") {
        r.go(l);
        return;
      }
      let f = Mh(
        l,
        JSON.parse(i),
        a,
        s.relative === "path"
      );
      e == null && t !== "/" && (f.pathname = f.pathname === "/" ? t : Kt([t, f.pathname])), (s.replace ? r.replace : r.push)(
        f,
        s.state,
        s
      );
    },
    [
      t,
      r,
      i,
      a,
      e
    ]
  );
}
var cA = Ge(null);
function fA(e) {
  let t = se(Jt).outlet;
  return ft(
    () => t && /* @__PURE__ */ ue(cA.Provider, { value: e }, t),
    [t, e]
  );
}
function ji(e, { relative: t } = {}) {
  let { matches: r } = se(Jt), { pathname: n } = pn(), a = JSON.stringify(Th(r));
  return ft(
    () => Mh(
      e,
      JSON.parse(a),
      n,
      t === "path"
    ),
    [e, a, n, t]
  );
}
function dA(e, t, r, n, a) {
  he(
    Mi(),
    // TODO: This error is probably because they somehow have 2 versions of the
    // router loaded. We can help them understand how to avoid that.
    "useRoutes() may be used only in the context of a <Router> component."
  );
  let { navigator: i } = se(Nt), { matches: o } = se(Jt), u = o[o.length - 1], l = u ? u.params : {}, s = u ? u.pathname : "/", f = u ? u.pathnameBase : "/", c = u && u.route;
  {
    let b = c && c.path || "";
    X1(
      s,
      !c || b.endsWith("*") || b.endsWith("*?"),
      `You rendered descendant <Routes> (or called \`useRoutes()\`) at "${s}" (under <Route path="${b}">) but the parent route path has no trailing "*". This means if you navigate deeper, the parent won't match anymore and therefore the child routes will never render.

Please change the parent <Route path="${b}"> to <Route path="${b === "/" ? "*" : `${b}/*`}">.`
    );
  }
  let d = pn(), h;
  h = d;
  let y = h.pathname || "/", v = y;
  if (f !== "/") {
    let b = f.replace(/^\//, "").split("/");
    v = "/" + y.replace(/^\//, "").split("/").slice(b.length).join("/");
  }
  let p = Er(e, { pathname: v });
  return We(
    c || p != null,
    `No routes matched location "${h.pathname}${h.search}${h.hash}" `
  ), We(
    p == null || p[p.length - 1].route.element !== void 0 || p[p.length - 1].route.Component !== void 0 || p[p.length - 1].route.lazy !== void 0,
    `Matched leaf route at location "${h.pathname}${h.search}${h.hash}" does not have an element or Component. This means it will render an <Outlet /> with a null value by default resulting in an "empty" page.`
  ), mA(
    p && p.map(
      (b) => Object.assign({}, b, {
        params: Object.assign({}, l, b.params),
        pathname: Kt([
          f,
          // Re-encode pathnames that were decoded inside matchRoutes.
          // Pre-encode `?` and `#` ahead of `encodeLocation` because it uses
          // `new URL()` internally and we need to prevent it from treating
          // them as separators
          i.encodeLocation ? i.encodeLocation(
            b.pathname.replace(/\?/g, "%3F").replace(/#/g, "%23")
          ).pathname : b.pathname
        ]),
        pathnameBase: b.pathnameBase === "/" ? f : Kt([
          f,
          // Re-encode pathnames that were decoded inside matchRoutes
          // Pre-encode `?` and `#` ahead of `encodeLocation` because it uses
          // `new URL()` internally and we need to prevent it from treating
          // them as separators
          i.encodeLocation ? i.encodeLocation(
            b.pathnameBase.replace(/\?/g, "%3F").replace(/#/g, "%23")
          ).pathname : b.pathnameBase
        ])
      })
    ),
    o,
    r,
    n,
    a
  );
}
function hA() {
  let e = OA(), t = Ua(e) ? `${e.status} ${e.statusText}` : e instanceof Error ? e.message : JSON.stringify(e), r = e instanceof Error ? e.stack : null, n = "rgba(200,200,200, 0.5)", a = { padding: "0.5rem", backgroundColor: n }, i = { padding: "2px 4px", backgroundColor: n }, o = null;
  return console.error(
    "Error handled by React Router default ErrorBoundary:",
    e
  ), o = /* @__PURE__ */ ue(Rn, null, /* @__PURE__ */ ue("p", null, "💿 Hey developer 👋"), /* @__PURE__ */ ue("p", null, "You can provide a way better UX than this when your app throws errors by providing your own ", /* @__PURE__ */ ue("code", { style: i }, "ErrorBoundary"), " or", " ", /* @__PURE__ */ ue("code", { style: i }, "errorElement"), " prop on your route.")), /* @__PURE__ */ ue(Rn, null, /* @__PURE__ */ ue("h2", null, "Unexpected Application Error!"), /* @__PURE__ */ ue("h3", { style: { fontStyle: "italic" } }, t), r ? /* @__PURE__ */ ue("pre", { style: a }, r) : null, o);
}
var pA = /* @__PURE__ */ ue(hA, null), V1 = class extends hu {
  constructor(e) {
    super(e), this.state = {
      location: e.location,
      revalidation: e.revalidation,
      error: e.error
    };
  }
  static getDerivedStateFromError(e) {
    return { error: e };
  }
  static getDerivedStateFromProps(e, t) {
    return t.location !== e.location || t.revalidation !== "idle" && e.revalidation === "idle" ? {
      error: e.error,
      location: e.location,
      revalidation: e.revalidation
    } : {
      error: e.error !== void 0 ? e.error : t.error,
      location: t.location,
      revalidation: e.revalidation || t.revalidation
    };
  }
  componentDidCatch(e, t) {
    this.props.onError ? this.props.onError(e, t) : console.error(
      "React Router caught the following error during render",
      e
    );
  }
  render() {
    let e = this.state.error;
    if (this.context && typeof e == "object" && e && "digest" in e && typeof e.digest == "string") {
      const r = uA(e.digest);
      r && (e = r);
    }
    let t = e !== void 0 ? /* @__PURE__ */ ue(Jt.Provider, { value: this.props.routeContext }, /* @__PURE__ */ ue(
      Rh.Provider,
      {
        value: e,
        children: this.props.component
      }
    )) : this.props.children;
    return this.context ? /* @__PURE__ */ ue(vA, { error: e }, t) : t;
  }
};
V1.contextType = U1;
var tl = /* @__PURE__ */ new WeakMap();
function vA({
  children: e,
  error: t
}) {
  let { basename: r } = se(Nt);
  if (typeof t == "object" && t && "digest" in t && typeof t.digest == "string") {
    let n = oA(t.digest);
    if (n) {
      let a = tl.get(t);
      if (a) throw a;
      let i = N1(n.location, r);
      if (j1 && !tl.get(t))
        if (i.isExternal || n.reloadDocument)
          window.location.href = i.absoluteURL || i.to;
        else {
          const o = Promise.resolve().then(
            () => window.__reactRouterDataRouter.navigate(i.to, {
              replace: n.replace
            })
          );
          throw tl.set(t, o), o;
        }
      return /* @__PURE__ */ ue(
        "meta",
        {
          httpEquiv: "refresh",
          content: `0;url=${i.absoluteURL || i.to}`
        }
      );
    }
  }
  return e;
}
function yA({ routeContext: e, match: t, children: r }) {
  let n = se(hn);
  return n && n.static && n.staticContext && (t.route.errorElement || t.route.ErrorBoundary) && (n.staticContext._deepestRenderedBoundaryId = t.route.id), /* @__PURE__ */ ue(Jt.Provider, { value: e }, r);
}
function mA(e, t = [], r = null, n = null, a = null) {
  if (e == null) {
    if (!r)
      return null;
    if (r.errors)
      e = r.matches;
    else if (t.length === 0 && !r.initialized && r.matches.length > 0)
      e = r.matches;
    else
      return null;
  }
  let i = e, o = r?.errors;
  if (o != null) {
    let f = i.findIndex(
      (c) => c.route.id && o?.[c.route.id] !== void 0
    );
    he(
      f >= 0,
      `Could not find a matching route for errors on route IDs: ${Object.keys(
        o
      ).join(",")}`
    ), i = i.slice(
      0,
      Math.min(i.length, f + 1)
    );
  }
  let u = !1, l = -1;
  if (r)
    for (let f = 0; f < i.length; f++) {
      let c = i[f];
      if ((c.route.HydrateFallback || c.route.hydrateFallbackElement) && (l = f), c.route.id) {
        let { loaderData: d, errors: h } = r, y = c.route.loader && !d.hasOwnProperty(c.route.id) && (!h || h[c.route.id] === void 0);
        if (c.route.lazy || y) {
          u = !0, l >= 0 ? i = i.slice(0, l + 1) : i = [i[0]];
          break;
        }
      }
    }
  let s = r && n ? (f, c) => {
    n(f, {
      location: r.location,
      params: r.matches?.[0]?.params ?? {},
      unstable_pattern: Ei(r.matches),
      errorInfo: c
    });
  } : void 0;
  return i.reduceRight(
    (f, c, d) => {
      let h, y = !1, v = null, p = null;
      r && (h = o && c.route.id ? o[c.route.id] : void 0, v = c.route.errorElement || pA, u && (l < 0 && d === 0 ? (X1(
        "route-fallback",
        !1,
        "No `HydrateFallback` element provided to render during initial hydration"
      ), y = !0, p = null) : l === d && (y = !0, p = c.route.hydrateFallbackElement || null)));
      let g = t.concat(i.slice(0, d + 1)), b = () => {
        let w;
        return h ? w = v : y ? w = p : c.route.Component ? w = /* @__PURE__ */ ue(c.route.Component, null) : c.route.element ? w = c.route.element : w = f, /* @__PURE__ */ ue(
          yA,
          {
            match: c,
            routeContext: {
              outlet: f,
              matches: g,
              isDataRoute: r != null
            },
            children: w
          }
        );
      };
      return r && (c.route.ErrorBoundary || c.route.errorElement || d === 0) ? /* @__PURE__ */ ue(
        V1,
        {
          location: r.location,
          revalidation: r.revalidation,
          component: v,
          error: h,
          children: b(),
          routeContext: { outlet: null, matches: g, isDataRoute: !0 },
          onError: s
        }
      ) : b();
    },
    null
  );
}
function Ih(e) {
  return `${e} must be used within a data router.  See https://reactrouter.com/en/main/routers/picking-a-router.`;
}
function gA(e) {
  let t = se(hn);
  return he(t, Ih(e)), t;
}
function bA(e) {
  let t = se(Ti);
  return he(t, Ih(e)), t;
}
function xA(e) {
  let t = se(Jt);
  return he(t, Ih(e)), t;
}
function Dh(e) {
  let t = xA(e), r = t.matches[t.matches.length - 1];
  return he(
    r.route.id,
    `${e} can only be used on routes that contain a unique "id"`
  ), r.route.id;
}
function wA() {
  return Dh(
    "useRouteId"
    /* UseRouteId */
  );
}
function OA() {
  let e = se(Rh), t = bA(
    "useRouteError"
    /* UseRouteError */
  ), r = Dh(
    "useRouteError"
    /* UseRouteError */
  );
  return e !== void 0 ? e : t.errors?.[r];
}
function _A() {
  let { router: e } = gA(
    "useNavigate"
    /* UseNavigateStable */
  ), t = Dh(
    "useNavigate"
    /* UseNavigateStable */
  ), r = pr(!1);
  return K1(() => {
    r.current = !0;
  }), dn(
    async (a, i = {}) => {
      We(r.current, G1), r.current && (typeof a == "number" ? await e.navigate(a) : await e.navigate(a, { fromRouteId: t, ...i }));
    },
    [e, t]
  );
}
var Pv = {};
function X1(e, t, r) {
  !t && !Pv[e] && (Pv[e] = !0, We(!1, r));
}
var Av = {};
function Ev(e, t) {
  !e && !Av[t] && (Av[t] = !0, console.warn(t));
}
var SA = "useOptimistic", Tv = WS[SA], PA = () => {
};
function AA(e) {
  return Tv ? Tv(e) : [e, PA];
}
function EA(e) {
  let t = {
    // Note: this check also occurs in createRoutesFromChildren so update
    // there if you change this -- please and thank you!
    hasErrorBoundary: e.hasErrorBoundary || e.ErrorBoundary != null || e.errorElement != null
  };
  return e.Component && (e.element && We(
    !1,
    "You should not include both `Component` and `element` on your route - `Component` will be used."
  ), Object.assign(t, {
    element: ue(e.Component),
    Component: void 0
  })), e.HydrateFallback && (e.hydrateFallbackElement && We(
    !1,
    "You should not include both `HydrateFallback` and `hydrateFallbackElement` on your route - `HydrateFallback` will be used."
  ), Object.assign(t, {
    hydrateFallbackElement: ue(e.HydrateFallback),
    HydrateFallback: void 0
  })), e.ErrorBoundary && (e.errorElement && We(
    !1,
    "You should not include both `ErrorBoundary` and `errorElement` on your route - `ErrorBoundary` will be used."
  ), Object.assign(t, {
    errorElement: ue(e.ErrorBoundary),
    ErrorBoundary: void 0
  })), t;
}
var TA = [
  "HydrateFallback",
  "hydrateFallbackElement"
], MA = class {
  constructor() {
    this.status = "pending", this.promise = new Promise((e, t) => {
      this.resolve = (r) => {
        this.status === "pending" && (this.status = "resolved", e(r));
      }, this.reject = (r) => {
        this.status === "pending" && (this.status = "rejected", t(r));
      };
    });
  }
};
function jA({
  router: e,
  flushSync: t,
  onError: r,
  unstable_useTransitions: n
}) {
  n = rA() || n;
  let [i, o] = Oe(e.state), [u, l] = AA(i), [s, f] = Oe(), [c, d] = Oe({
    isTransitioning: !1
  }), [h, y] = Oe(), [v, p] = Oe(), [g, b] = Oe(), w = pr(/* @__PURE__ */ new Map()), _ = dn(
    (S, { deletedFetchers: T, newErrors: C, flushSync: A, viewTransitionOpts: N }) => {
      C && r && Object.values(C).forEach(
        (D) => r(D, {
          location: S.location,
          params: S.matches[0]?.params ?? {},
          unstable_pattern: Ei(S.matches)
        })
      ), S.fetchers.forEach((D, R) => {
        D.data !== void 0 && w.current.set(R, D.data);
      }), T.forEach((D) => w.current.delete(D)), Ev(
        A === !1 || t != null,
        'You provided the `flushSync` option to a router update, but you are not using the `<RouterProvider>` from `react-router/dom` so `ReactDOM.flushSync()` is unavailable.  Please update your app to `import { RouterProvider } from "react-router/dom"` and ensure you have `react-dom` installed as a dependency to use the `flushSync` option.'
      );
      let $ = e.window != null && e.window.document != null && typeof e.window.document.startViewTransition == "function";
      if (Ev(
        N == null || $,
        "You provided the `viewTransition` option to a router update, but you do not appear to be running in a DOM environment as `window.startViewTransition` is not available."
      ), !N || !$) {
        t && A ? t(() => o(S)) : n === !1 ? o(S) : Ba(() => {
          n === !0 && l((D) => Mv(D, S)), o(S);
        });
        return;
      }
      if (t && A) {
        t(() => {
          v && (h?.resolve(), v.skipTransition()), d({
            isTransitioning: !0,
            flushSync: !0,
            currentLocation: N.currentLocation,
            nextLocation: N.nextLocation
          });
        });
        let D = e.window.document.startViewTransition(() => {
          t(() => o(S));
        });
        D.finished.finally(() => {
          t(() => {
            y(void 0), p(void 0), f(void 0), d({ isTransitioning: !1 });
          });
        }), t(() => p(D));
        return;
      }
      v ? (h?.resolve(), v.skipTransition(), b({
        state: S,
        currentLocation: N.currentLocation,
        nextLocation: N.nextLocation
      })) : (f(S), d({
        isTransitioning: !0,
        flushSync: !1,
        currentLocation: N.currentLocation,
        nextLocation: N.nextLocation
      }));
    },
    [
      e.window,
      t,
      v,
      h,
      n,
      l,
      r
    ]
  );
  Ah(() => e.subscribe(_), [e, _]), It(() => {
    c.isTransitioning && !c.flushSync && y(new MA());
  }, [c]), It(() => {
    if (h && s && e.window) {
      let S = s, T = h.promise, C = e.window.document.startViewTransition(async () => {
        n === !1 ? o(S) : Ba(() => {
          n === !0 && l((A) => Mv(A, S)), o(S);
        }), await T;
      });
      C.finished.finally(() => {
        y(void 0), p(void 0), f(void 0), d({ isTransitioning: !1 });
      }), p(C);
    }
  }, [
    s,
    h,
    e.window,
    n,
    l
  ]), It(() => {
    h && s && u.location.key === s.location.key && h.resolve();
  }, [h, v, u.location, s]), It(() => {
    !c.isTransitioning && g && (f(g.state), d({
      isTransitioning: !0,
      flushSync: !1,
      currentLocation: g.currentLocation,
      nextLocation: g.nextLocation
    }), b(void 0));
  }, [c.isTransitioning, g]);
  let m = ft(() => ({
    createHref: e.createHref,
    encodeLocation: e.encodeLocation,
    go: (S) => e.navigate(S),
    push: (S, T, C) => e.navigate(S, {
      state: T,
      preventScrollReset: C?.preventScrollReset
    }),
    replace: (S, T, C) => e.navigate(S, {
      replace: !0,
      state: T,
      preventScrollReset: C?.preventScrollReset
    })
  }), [e]), O = e.basename || "/", x = ft(
    () => ({
      router: e,
      navigator: m,
      static: !1,
      basename: O,
      onError: r
    }),
    [e, m, O, r]
  );
  return /* @__PURE__ */ ue(Rn, null, /* @__PURE__ */ ue(hn.Provider, { value: x }, /* @__PURE__ */ ue(Ti.Provider, { value: u }, /* @__PURE__ */ ue(W1.Provider, { value: w.current }, /* @__PURE__ */ ue($h.Provider, { value: c }, /* @__PURE__ */ ue(
    RA,
    {
      basename: O,
      location: u.location,
      navigationType: u.historyAction,
      navigator: m,
      unstable_useTransitions: n
    },
    /* @__PURE__ */ ue(
      NA,
      {
        routes: e.routes,
        future: e.future,
        state: u,
        onError: r
      }
    )
  ))))), null);
}
function Mv(e, t) {
  return {
    // Don't surface "current location specific" stuff mid-navigation
    // (historyAction, location, matches, loaderData, errors, initialized,
    // restoreScroll, preventScrollReset, blockers, etc.)
    ...e,
    // Only surface "pending/in-flight stuff"
    // (navigation, revalidation, actionData, fetchers, )
    navigation: t.navigation.state !== "idle" ? t.navigation : e.navigation,
    revalidation: t.revalidation !== "idle" ? t.revalidation : e.revalidation,
    actionData: t.navigation.state !== "submitting" ? t.actionData : e.actionData,
    fetchers: t.fetchers
  };
}
var NA = O1(CA);
function CA({
  routes: e,
  future: t,
  state: r,
  onError: n
}) {
  return dA(e, void 0, r, n, t);
}
function $A(e) {
  return fA(e.context);
}
function RA({
  basename: e = "/",
  children: t = null,
  location: r,
  navigationType: n = "POP",
  navigator: a,
  static: i = !1,
  unstable_useTransitions: o
}) {
  he(
    !Mi(),
    "You cannot render a <Router> inside another <Router>. You should never have more than one in your app."
  );
  let u = e.replace(/^\/*/, "/"), l = ft(
    () => ({
      basename: u,
      navigator: a,
      static: i,
      unstable_useTransitions: o,
      future: {}
    }),
    [u, a, i, o]
  );
  typeof r == "string" && (r = Dr(r));
  let {
    pathname: s = "/",
    search: f = "",
    hash: c = "",
    state: d = null,
    key: h = "default"
  } = r, y = ft(() => {
    let v = Mt(s, u);
    return v == null ? null : {
      location: {
        pathname: v,
        search: f,
        hash: c,
        state: d,
        key: h
      },
      navigationType: n
    };
  }, [u, s, f, c, d, h, n]);
  return We(
    y != null,
    `<Router basename="${u}"> is not able to match the URL "${s}${f}${c}" because it does not start with the basename, so the <Router> won't render anything.`
  ), y == null ? null : /* @__PURE__ */ ue(Nt.Provider, { value: l }, /* @__PURE__ */ ue(pu.Provider, { children: t, value: y }));
}
var co = "get", fo = "application/x-www-form-urlencoded";
function vu(e) {
  return typeof HTMLElement < "u" && e instanceof HTMLElement;
}
function kA(e) {
  return vu(e) && e.tagName.toLowerCase() === "button";
}
function IA(e) {
  return vu(e) && e.tagName.toLowerCase() === "form";
}
function DA(e) {
  return vu(e) && e.tagName.toLowerCase() === "input";
}
function LA(e) {
  return !!(e.metaKey || e.altKey || e.ctrlKey || e.shiftKey);
}
function qA(e, t) {
  return e.button === 0 && // Ignore everything but left clicks
  (!t || t === "_self") && // Let browser handle "target=_blank" etc.
  !LA(e);
}
var Vi = null;
function BA() {
  if (Vi === null)
    try {
      new FormData(
        document.createElement("form"),
        // @ts-expect-error if FormData supports the submitter parameter, this will throw
        0
      ), Vi = !1;
    } catch {
      Vi = !0;
    }
  return Vi;
}
var FA = /* @__PURE__ */ new Set([
  "application/x-www-form-urlencoded",
  "multipart/form-data",
  "text/plain"
]);
function rl(e) {
  return e != null && !FA.has(e) ? (We(
    !1,
    `"${e}" is not a valid \`encType\` for \`<Form>\`/\`<fetcher.Form>\` and will default to "${fo}"`
  ), null) : e;
}
function zA(e, t) {
  let r, n, a, i, o;
  if (IA(e)) {
    let u = e.getAttribute("action");
    n = u ? Mt(u, t) : null, r = e.getAttribute("method") || co, a = rl(e.getAttribute("enctype")) || fo, i = new FormData(e);
  } else if (kA(e) || DA(e) && (e.type === "submit" || e.type === "image")) {
    let u = e.form;
    if (u == null)
      throw new Error(
        'Cannot submit a <button> or <input type="submit"> without a <form>'
      );
    let l = e.getAttribute("formaction") || u.getAttribute("action");
    if (n = l ? Mt(l, t) : null, r = e.getAttribute("formmethod") || u.getAttribute("method") || co, a = rl(e.getAttribute("formenctype")) || rl(u.getAttribute("enctype")) || fo, i = new FormData(u, e), !BA()) {
      let { name: s, type: f, value: c } = e;
      if (f === "image") {
        let d = s ? `${s}.` : "";
        i.append(`${d}x`, "0"), i.append(`${d}y`, "0");
      } else s && i.append(s, c);
    }
  } else {
    if (vu(e))
      throw new Error(
        'Cannot submit element that is not <form>, <button>, or <input type="submit|image">'
      );
    r = co, n = null, a = fo, o = e;
  }
  return i && a === "text/plain" && (o = i, i = void 0), { action: n, method: r.toLowerCase(), encType: a, formData: i, body: o };
}
Object.getOwnPropertyNames(Object.prototype).sort().join("\0");
function Lh(e, t) {
  if (e === !1 || e === null || typeof e > "u")
    throw new Error(t);
}
function UA(e, t, r, n) {
  let a = typeof e == "string" ? new URL(
    e,
    // This can be called during the SSR flow via PrefetchPageLinksImpl so
    // don't assume window is available
    typeof window > "u" ? "server://singlefetch/" : window.location.origin
  ) : e;
  return r ? a.pathname.endsWith("/") ? a.pathname = `${a.pathname}_.${n}` : a.pathname = `${a.pathname}.${n}` : a.pathname === "/" ? a.pathname = `_root.${n}` : t && Mt(a.pathname, t) === "/" ? a.pathname = `${t.replace(/\/$/, "")}/_root.${n}` : a.pathname = `${a.pathname.replace(/\/$/, "")}.${n}`, a;
}
async function WA(e, t) {
  if (e.id in t)
    return t[e.id];
  try {
    let r = await import(
      /* @vite-ignore */
      /* webpackIgnore: true */
      e.module
    );
    return t[e.id] = r, r;
  } catch (r) {
    return console.error(
      `Error loading route module \`${e.module}\`, reloading page...`
    ), console.error(r), window.__reactRouterContext && window.__reactRouterContext.isSpaMode, window.location.reload(), new Promise(() => {
    });
  }
}
function HA(e) {
  return e == null ? !1 : e.href == null ? e.rel === "preload" && typeof e.imageSrcSet == "string" && typeof e.imageSizes == "string" : typeof e.rel == "string" && typeof e.href == "string";
}
async function GA(e, t, r) {
  let n = await Promise.all(
    e.map(async (a) => {
      let i = t.routes[a.route.id];
      if (i) {
        let o = await WA(i, r);
        return o.links ? o.links() : [];
      }
      return [];
    })
  );
  return YA(
    n.flat(1).filter(HA).filter((a) => a.rel === "stylesheet" || a.rel === "preload").map(
      (a) => a.rel === "stylesheet" ? { ...a, rel: "prefetch", as: "style" } : { ...a, rel: "prefetch" }
    )
  );
}
function jv(e, t, r, n, a, i) {
  let o = (l, s) => r[s] ? l.route.id !== r[s].route.id : !0, u = (l, s) => (
    // param change, /users/123 -> /users/456
    r[s].pathname !== l.pathname || // splat param changed, which is not present in match.path
    // e.g. /files/images/avatar.jpg -> files/finances.xls
    r[s].route.path?.endsWith("*") && r[s].params["*"] !== l.params["*"]
  );
  return i === "assets" ? t.filter(
    (l, s) => o(l, s) || u(l, s)
  ) : i === "data" ? t.filter((l, s) => {
    let f = n.routes[l.route.id];
    if (!f || !f.hasLoader)
      return !1;
    if (o(l, s) || u(l, s))
      return !0;
    if (l.route.shouldRevalidate) {
      let c = l.route.shouldRevalidate({
        currentUrl: new URL(
          a.pathname + a.search + a.hash,
          window.origin
        ),
        currentParams: r[0]?.params || {},
        nextUrl: new URL(e, window.origin),
        nextParams: l.params,
        defaultShouldRevalidate: !0
      });
      if (typeof c == "boolean")
        return c;
    }
    return !0;
  }) : [];
}
function KA(e, t, { includeHydrateFallback: r } = {}) {
  return VA(
    e.map((n) => {
      let a = t.routes[n.route.id];
      if (!a) return [];
      let i = [a.module];
      return a.clientActionModule && (i = i.concat(a.clientActionModule)), a.clientLoaderModule && (i = i.concat(a.clientLoaderModule)), r && a.hydrateFallbackModule && (i = i.concat(a.hydrateFallbackModule)), a.imports && (i = i.concat(a.imports)), i;
    }).flat(1)
  );
}
function VA(e) {
  return [...new Set(e)];
}
function XA(e) {
  let t = {}, r = Object.keys(e).sort();
  for (let n of r)
    t[n] = e[n];
  return t;
}
function YA(e, t) {
  let r = /* @__PURE__ */ new Set();
  return new Set(t), e.reduce((n, a) => {
    let i = JSON.stringify(XA(a));
    return r.has(i) || (r.add(i), n.push({ key: i, link: a })), n;
  }, []);
}
function Y1() {
  let e = se(hn);
  return Lh(
    e,
    "You must render this element inside a <DataRouterContext.Provider> element"
  ), e;
}
function ZA() {
  let e = se(Ti);
  return Lh(
    e,
    "You must render this element inside a <DataRouterStateContext.Provider> element"
  ), e;
}
var qh = Ge(void 0);
qh.displayName = "FrameworkContext";
function Z1() {
  let e = se(qh);
  return Lh(
    e,
    "You must render this element inside a <HydratedRouter> element"
  ), e;
}
function JA(e, t) {
  let r = se(qh), [n, a] = Oe(!1), [i, o] = Oe(!1), { onFocus: u, onBlur: l, onMouseEnter: s, onMouseLeave: f, onTouchStart: c } = t, d = pr(null);
  It(() => {
    if (e === "render" && o(!0), e === "viewport") {
      let v = (g) => {
        g.forEach((b) => {
          o(b.isIntersecting);
        });
      }, p = new IntersectionObserver(v, { threshold: 0.5 });
      return d.current && p.observe(d.current), () => {
        p.disconnect();
      };
    }
  }, [e]), It(() => {
    if (n) {
      let v = setTimeout(() => {
        o(!0);
      }, 100);
      return () => {
        clearTimeout(v);
      };
    }
  }, [n]);
  let h = () => {
    a(!0);
  }, y = () => {
    a(!1), o(!1);
  };
  return r ? e !== "intent" ? [i, d, {}] : [
    i,
    d,
    {
      onFocus: ga(u, h),
      onBlur: ga(l, y),
      onMouseEnter: ga(s, h),
      onMouseLeave: ga(f, y),
      onTouchStart: ga(c, h)
    }
  ] : [!1, d, {}];
}
function ga(e, t) {
  return (r) => {
    e && e(r), r.defaultPrevented || t(r);
  };
}
function QA({ page: e, ...t }) {
  let { router: r } = Y1(), n = ft(
    () => Er(r.routes, e, r.basename),
    [r.routes, e, r.basename]
  );
  return n ? /* @__PURE__ */ ue(t2, { page: e, matches: n, ...t }) : null;
}
function e2(e) {
  let { manifest: t, routeModules: r } = Z1(), [n, a] = Oe([]);
  return It(() => {
    let i = !1;
    return GA(e, t, r).then(
      (o) => {
        i || a(o);
      }
    ), () => {
      i = !0;
    };
  }, [e, t, r]), n;
}
function t2({
  page: e,
  matches: t,
  ...r
}) {
  let n = pn(), { future: a, manifest: i, routeModules: o } = Z1(), { basename: u } = Y1(), { loaderData: l, matches: s } = ZA(), f = ft(
    () => jv(
      e,
      t,
      s,
      i,
      n,
      "data"
    ),
    [e, t, s, i, n]
  ), c = ft(
    () => jv(
      e,
      t,
      s,
      i,
      n,
      "assets"
    ),
    [e, t, s, i, n]
  ), d = ft(() => {
    if (e === n.pathname + n.search + n.hash)
      return [];
    let v = /* @__PURE__ */ new Set(), p = !1;
    if (t.forEach((b) => {
      let w = i.routes[b.route.id];
      !w || !w.hasLoader || (!f.some((_) => _.route.id === b.route.id) && b.route.id in l && o[b.route.id]?.shouldRevalidate || w.hasClientLoader ? p = !0 : v.add(b.route.id));
    }), v.size === 0)
      return [];
    let g = UA(
      e,
      u,
      a.unstable_trailingSlashAwareDataRequests,
      "data"
    );
    return p && v.size > 0 && g.searchParams.set(
      "_routes",
      t.filter((b) => v.has(b.route.id)).map((b) => b.route.id).join(",")
    ), [g.pathname + g.search];
  }, [
    u,
    a.unstable_trailingSlashAwareDataRequests,
    l,
    n,
    i,
    f,
    t,
    e,
    o
  ]), h = ft(
    () => KA(c, i),
    [c, i]
  ), y = e2(c);
  return /* @__PURE__ */ ue(Rn, null, d.map((v) => /* @__PURE__ */ ue("link", { key: v, rel: "prefetch", as: "fetch", href: v, ...r })), h.map((v) => /* @__PURE__ */ ue("link", { key: v, rel: "modulepreload", href: v, ...r })), y.map(({ key: v, link: p }) => (
    // these don't spread `linkProps` because they are full link descriptors
    // already with their own props
    /* @__PURE__ */ ue(
      "link",
      {
        key: v,
        nonce: r.nonce,
        ...p,
        crossOrigin: p.crossOrigin ?? r.crossOrigin
      }
    )
  )));
}
function r2(...e) {
  return (t) => {
    e.forEach((r) => {
      typeof r == "function" ? r(t) : r != null && (r.current = t);
    });
  };
}
var n2 = typeof window < "u" && typeof window.document < "u" && typeof window.document.createElement < "u";
try {
  n2 && (window.__reactRouterVersion = // @ts-expect-error
  "7.13.0");
} catch {
}
function a2(e, t) {
  return NP({
    basename: t?.basename,
    getContext: t?.getContext,
    future: t?.future,
    history: KS({ window: t?.window }),
    hydrationData: i2(),
    routes: e,
    mapRouteProperties: EA,
    hydrationRouteProperties: TA,
    dataStrategy: t?.dataStrategy,
    patchRoutesOnNavigation: t?.patchRoutesOnNavigation,
    window: t?.window,
    unstable_instrumentations: t?.unstable_instrumentations
  }).initialize();
}
function i2() {
  let e = window?.__staticRouterHydrationData;
  return e && e.errors && (e = {
    ...e,
    errors: o2(e.errors)
  }), e;
}
function o2(e) {
  if (!e) return null;
  let t = Object.entries(e), r = {};
  for (let [n, a] of t)
    if (a && a.__type === "RouteErrorResponse")
      r[n] = new Ai(
        a.status,
        a.statusText,
        a.data,
        a.internal === !0
      );
    else if (a && a.__type === "Error") {
      if (a.__subType) {
        let i = window[a.__subType];
        if (typeof i == "function")
          try {
            let o = new i(a.message);
            o.stack = "", r[n] = o;
          } catch {
          }
      }
      if (r[n] == null) {
        let i = new Error(a.message);
        i.stack = "", r[n] = i;
      }
    } else
      r[n] = a;
  return r;
}
var J1 = /^(?:[a-z][a-z0-9+.-]*:|\/\/)/i, Q1 = Ir(
  function({
    onClick: t,
    discover: r = "render",
    prefetch: n = "none",
    relative: a,
    reloadDocument: i,
    replace: o,
    state: u,
    target: l,
    to: s,
    preventScrollReset: f,
    viewTransition: c,
    unstable_defaultShouldRevalidate: d,
    ...h
  }, y) {
    let { basename: v, unstable_useTransitions: p } = se(Nt), g = typeof s == "string" && J1.test(s), b = N1(s, v);
    s = b.to;
    let w = lA(s, { relative: a }), [_, m, O] = JA(
      n,
      h
    ), x = s2(s, {
      replace: o,
      state: u,
      target: l,
      preventScrollReset: f,
      relative: a,
      viewTransition: c,
      unstable_defaultShouldRevalidate: d,
      unstable_useTransitions: p
    });
    function S(C) {
      t && t(C), C.defaultPrevented || x(C);
    }
    let T = (
      // eslint-disable-next-line jsx-a11y/anchor-has-content
      /* @__PURE__ */ ue(
        "a",
        {
          ...h,
          ...O,
          href: b.absoluteURL || w,
          onClick: b.isExternal || i ? t : S,
          ref: r2(y, m),
          target: l,
          "data-discover": !g && r === "render" ? "true" : void 0
        }
      )
    );
    return _ && !g ? /* @__PURE__ */ ue(Rn, null, T, /* @__PURE__ */ ue(QA, { page: w })) : T;
  }
);
Q1.displayName = "Link";
var ew = Ir(
  function({
    "aria-current": t = "page",
    caseSensitive: r = !1,
    className: n = "",
    end: a = !1,
    style: i,
    to: o,
    viewTransition: u,
    children: l,
    ...s
  }, f) {
    let c = ji(o, { relative: s.relative }), d = pn(), h = se(Ti), { navigator: y, basename: v } = se(Nt), p = h != null && // Conditional usage is OK here because the usage of a data router is static
    // eslint-disable-next-line react-hooks/rules-of-hooks
    p2(c) && u === !0, g = y.encodeLocation ? y.encodeLocation(c).pathname : c.pathname, b = d.pathname, w = h && h.navigation && h.navigation.location ? h.navigation.location.pathname : null;
    r || (b = b.toLowerCase(), w = w ? w.toLowerCase() : null, g = g.toLowerCase()), w && v && (w = Mt(w, v) || w);
    const _ = g !== "/" && g.endsWith("/") ? g.length - 1 : g.length;
    let m = b === g || !a && b.startsWith(g) && b.charAt(_) === "/", O = w != null && (w === g || !a && w.startsWith(g) && w.charAt(g.length) === "/"), x = {
      isActive: m,
      isPending: O,
      isTransitioning: p
    }, S = m ? t : void 0, T;
    typeof n == "function" ? T = n(x) : T = [
      n,
      m ? "active" : null,
      O ? "pending" : null,
      p ? "transitioning" : null
    ].filter(Boolean).join(" ");
    let C = typeof i == "function" ? i(x) : i;
    return /* @__PURE__ */ ue(
      Q1,
      {
        ...s,
        "aria-current": S,
        className: T,
        ref: f,
        style: C,
        to: o,
        viewTransition: u
      },
      typeof l == "function" ? l(x) : l
    );
  }
);
ew.displayName = "NavLink";
var u2 = Ir(
  ({
    discover: e = "render",
    fetcherKey: t,
    navigate: r,
    reloadDocument: n,
    replace: a,
    state: i,
    method: o = co,
    action: u,
    onSubmit: l,
    relative: s,
    preventScrollReset: f,
    viewTransition: c,
    unstable_defaultShouldRevalidate: d,
    ...h
  }, y) => {
    let { unstable_useTransitions: v } = se(Nt), p = d2(), g = h2(u, { relative: s }), b = o.toLowerCase() === "get" ? "get" : "post", w = typeof u == "string" && J1.test(u);
    return /* @__PURE__ */ ue(
      "form",
      {
        ref: y,
        method: b,
        action: g,
        onSubmit: n ? l : (m) => {
          if (l && l(m), m.defaultPrevented) return;
          m.preventDefault();
          let O = m.nativeEvent.submitter, x = O?.getAttribute("formmethod") || o, S = () => p(O || m.currentTarget, {
            fetcherKey: t,
            method: x,
            navigate: r,
            replace: a,
            state: i,
            relative: s,
            preventScrollReset: f,
            viewTransition: c,
            unstable_defaultShouldRevalidate: d
          });
          v && r !== !1 ? Ba(() => S()) : S();
        },
        ...h,
        "data-discover": !w && e === "render" ? "true" : void 0
      }
    );
  }
);
u2.displayName = "Form";
function l2(e) {
  return `${e} must be used within a data router.  See https://reactrouter.com/en/main/routers/picking-a-router.`;
}
function tw(e) {
  let t = se(hn);
  return he(t, l2(e)), t;
}
function s2(e, {
  target: t,
  replace: r,
  state: n,
  preventScrollReset: a,
  relative: i,
  viewTransition: o,
  unstable_defaultShouldRevalidate: u,
  unstable_useTransitions: l
} = {}) {
  let s = kh(), f = pn(), c = ji(e, { relative: i });
  return dn(
    (d) => {
      if (qA(d, t)) {
        d.preventDefault();
        let h = r !== void 0 ? r : Yt(f) === Yt(c), y = () => s(e, {
          replace: h,
          state: n,
          preventScrollReset: a,
          relative: i,
          viewTransition: o,
          unstable_defaultShouldRevalidate: u
        });
        l ? Ba(() => y()) : y();
      }
    },
    [
      f,
      s,
      c,
      r,
      n,
      t,
      e,
      a,
      i,
      o,
      u,
      l
    ]
  );
}
var c2 = 0, f2 = () => `__${String(++c2)}__`;
function d2() {
  let { router: e } = tw(
    "useSubmit"
    /* UseSubmit */
  ), { basename: t } = se(Nt), r = wA(), n = e.fetch, a = e.navigate;
  return dn(
    async (i, o = {}) => {
      let { action: u, method: l, encType: s, formData: f, body: c } = zA(
        i,
        t
      );
      if (o.navigate === !1) {
        let d = o.fetcherKey || f2();
        await n(d, r, o.action || u, {
          unstable_defaultShouldRevalidate: o.unstable_defaultShouldRevalidate,
          preventScrollReset: o.preventScrollReset,
          formData: f,
          body: c,
          formMethod: o.method || l,
          formEncType: o.encType || s,
          flushSync: o.flushSync
        });
      } else
        await a(o.action || u, {
          unstable_defaultShouldRevalidate: o.unstable_defaultShouldRevalidate,
          preventScrollReset: o.preventScrollReset,
          formData: f,
          body: c,
          formMethod: o.method || l,
          formEncType: o.encType || s,
          replace: o.replace,
          state: o.state,
          fromRouteId: r,
          flushSync: o.flushSync,
          viewTransition: o.viewTransition
        });
    },
    [n, a, t, r]
  );
}
function h2(e, { relative: t } = {}) {
  let { basename: r } = se(Nt), n = se(Jt);
  he(n, "useFormAction must be used inside a RouteContext");
  let [a] = n.matches.slice(-1), i = { ...ji(e || ".", { relative: t }) }, o = pn();
  if (e == null) {
    i.search = o.search;
    let u = new URLSearchParams(i.search), l = u.getAll("index");
    if (l.some((f) => f === "")) {
      u.delete("index"), l.filter((c) => c).forEach((c) => u.append("index", c));
      let f = u.toString();
      i.search = f ? `?${f}` : "";
    }
  }
  return (!e || e === ".") && a.route.index && (i.search = i.search ? i.search.replace(/^\?/, "?index&") : "?index"), r !== "/" && (i.pathname = i.pathname === "/" ? r : Kt([r, i.pathname])), Yt(i);
}
function p2(e, { relative: t } = {}) {
  let r = se($h);
  he(
    r != null,
    "`useViewTransitionState` must be used within `react-router-dom`'s `RouterProvider`.  Did you accidentally import `RouterProvider` from `react-router`?"
  );
  let { basename: n } = tw(
    "useViewTransitionState"
    /* useViewTransitionState */
  ), a = ji(e, { relative: t });
  if (!r.isTransitioning)
    return !1;
  let i = Mt(r.currentLocation.pathname, n) || r.currentLocation.pathname, o = Mt(r.nextLocation.pathname, n) || r.nextLocation.pathname;
  return ho(a.pathname, o) != null || ho(a.pathname, i) != null;
}
var Xi = typeof globalThis < "u" ? globalThis : typeof window < "u" ? window : typeof global < "u" ? global : typeof self < "u" ? self : {};
function $e(e) {
  return e && e.__esModule && Object.prototype.hasOwnProperty.call(e, "default") ? e.default : e;
}
/**
 * @license lucide-react v0.487.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */
const v2 = (e) => e.replace(/([a-z0-9])([A-Z])/g, "$1-$2").toLowerCase(), y2 = (e) => e.replace(
  /^([A-Z])|[\s-_]+(\w)/g,
  (t, r, n) => n ? n.toUpperCase() : r.toLowerCase()
), Nv = (e) => {
  const t = y2(e);
  return t.charAt(0).toUpperCase() + t.slice(1);
}, rw = (...e) => e.filter((t, r, n) => !!t && t.trim() !== "" && n.indexOf(t) === r).join(" ").trim();
/**
 * @license lucide-react v0.487.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */
var m2 = {
  xmlns: "http://www.w3.org/2000/svg",
  width: 24,
  height: 24,
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  strokeWidth: 2,
  strokeLinecap: "round",
  strokeLinejoin: "round"
};
/**
 * @license lucide-react v0.487.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */
const g2 = Ir(
  ({
    color: e = "currentColor",
    size: t = 24,
    strokeWidth: r = 2,
    absoluteStrokeWidth: n,
    className: a = "",
    children: i,
    iconNode: o,
    ...u
  }, l) => ue(
    "svg",
    {
      ref: l,
      ...m2,
      width: t,
      height: t,
      stroke: e,
      strokeWidth: n ? Number(r) * 24 / Number(t) : r,
      className: rw("lucide", a),
      ...u
    },
    [
      ...o.map(([s, f]) => ue(s, f)),
      ...Array.isArray(i) ? i : [i]
    ]
  )
);
/**
 * @license lucide-react v0.487.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */
const ve = (e, t) => {
  const r = Ir(
    ({ className: n, ...a }, i) => ue(g2, {
      ref: i,
      iconNode: t,
      className: rw(
        `lucide-${v2(Nv(e))}`,
        `lucide-${e}`,
        n
      ),
      ...a
    })
  );
  return r.displayName = Nv(e), r;
};
/**
 * @license lucide-react v0.487.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */
const b2 = [
  [
    "path",
    {
      d: "M22 12h-2.48a2 2 0 0 0-1.93 1.46l-2.35 8.36a.25.25 0 0 1-.48 0L9.24 2.18a.25.25 0 0 0-.48 0l-2.35 8.36A2 2 0 0 1 4.49 12H2",
      key: "169zse"
    }
  ]
], x2 = ve("activity", b2);
/**
 * @license lucide-react v0.487.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */
const w2 = [
  ["path", { d: "M5 12h14", key: "1ays0h" }],
  ["path", { d: "m12 5 7 7-7 7", key: "xquz4c" }]
], O2 = ve("arrow-right", w2);
/**
 * @license lucide-react v0.487.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */
const _2 = [
  [
    "path",
    {
      d: "m15.477 12.89 1.515 8.526a.5.5 0 0 1-.81.47l-3.58-2.687a1 1 0 0 0-1.197 0l-3.586 2.686a.5.5 0 0 1-.81-.469l1.514-8.526",
      key: "1yiouv"
    }
  ],
  ["circle", { cx: "12", cy: "8", r: "6", key: "1vp47v" }]
], S2 = ve("award", _2);
/**
 * @license lucide-react v0.487.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */
const P2 = [
  ["path", { d: "M10.268 21a2 2 0 0 0 3.464 0", key: "vwvbt9" }],
  [
    "path",
    {
      d: "M3.262 15.326A1 1 0 0 0 4 17h16a1 1 0 0 0 .74-1.673C19.41 13.956 18 12.499 18 8A6 6 0 0 0 6 8c0 4.499-1.411 5.956-2.738 7.326",
      key: "11g9vi"
    }
  ]
], A2 = ve("bell", P2);
/**
 * @license lucide-react v0.487.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */
const E2 = [
  ["path", { d: "M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z", key: "1b4qmf" }],
  ["path", { d: "M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2", key: "i71pzd" }],
  ["path", { d: "M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2", key: "10jefs" }],
  ["path", { d: "M10 6h4", key: "1itunk" }],
  ["path", { d: "M10 10h4", key: "tcdvrf" }],
  ["path", { d: "M10 14h4", key: "kelpxr" }],
  ["path", { d: "M10 18h4", key: "1ulq68" }]
], Cv = ve("building-2", E2);
/**
 * @license lucide-react v0.487.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */
const T2 = [
  ["path", { d: "M8 2v4", key: "1cmpym" }],
  ["path", { d: "M16 2v4", key: "4m81vk" }],
  ["rect", { width: "18", height: "18", x: "3", y: "4", rx: "2", key: "1hopcy" }],
  ["path", { d: "M3 10h18", key: "8toen8" }]
], nw = ve("calendar", T2);
/**
 * @license lucide-react v0.487.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */
const M2 = [
  [
    "path",
    {
      d: "M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z",
      key: "1tc9qg"
    }
  ],
  ["circle", { cx: "12", cy: "13", r: "3", key: "1vg3eu" }]
], j2 = ve("camera", M2);
/**
 * @license lucide-react v0.487.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */
const N2 = [["path", { d: "m6 9 6 6 6-6", key: "qrunsl" }]], C2 = ve("chevron-down", N2);
/**
 * @license lucide-react v0.487.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */
const $2 = [["path", { d: "m15 18-6-6 6-6", key: "1wnfg3" }]], R2 = ve("chevron-left", $2);
/**
 * @license lucide-react v0.487.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */
const k2 = [["path", { d: "m9 18 6-6-6-6", key: "mthhwq" }]], aw = ve("chevron-right", k2);
/**
 * @license lucide-react v0.487.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */
const I2 = [
  ["circle", { cx: "12", cy: "12", r: "10", key: "1mglay" }],
  ["line", { x1: "12", x2: "12", y1: "8", y2: "12", key: "1pkeuh" }],
  ["line", { x1: "12", x2: "12.01", y1: "16", y2: "16", key: "4dfq90" }]
], D2 = ve("circle-alert", I2);
/**
 * @license lucide-react v0.487.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */
const L2 = [
  ["circle", { cx: "12", cy: "12", r: "10", key: "1mglay" }],
  ["path", { d: "m9 12 2 2 4-4", key: "dzmm74" }]
], Bh = ve("circle-check", L2);
/**
 * @license lucide-react v0.487.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */
const q2 = [
  ["rect", { width: "8", height: "4", x: "8", y: "2", rx: "1", ry: "1", key: "tgr4d6" }],
  [
    "path",
    {
      d: "M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2",
      key: "116196"
    }
  ],
  ["path", { d: "M12 11h4", key: "1jrz19" }],
  ["path", { d: "M12 16h4", key: "n85exb" }],
  ["path", { d: "M8 11h.01", key: "1dfujw" }],
  ["path", { d: "M8 16h.01", key: "18s6g9" }]
], iw = ve("clipboard-list", q2);
/**
 * @license lucide-react v0.487.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */
const B2 = [
  ["circle", { cx: "12", cy: "12", r: "10", key: "1mglay" }],
  ["polyline", { points: "12 6 12 12 16 14", key: "68esgv" }]
], Fh = ve("clock", B2);
/**
 * @license lucide-react v0.487.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */
const F2 = [
  ["line", { x1: "12", x2: "12", y1: "2", y2: "22", key: "7eqyqh" }],
  ["path", { d: "M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6", key: "1b0p4s" }]
], yu = ve("dollar-sign", F2);
/**
 * @license lucide-react v0.487.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */
const z2 = [
  ["path", { d: "M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4", key: "ih7n3h" }],
  ["polyline", { points: "7 10 12 15 17 10", key: "2ggqvy" }],
  ["line", { x1: "12", x2: "12", y1: "15", y2: "3", key: "1vk2je" }]
], U2 = ve("download", z2);
/**
 * @license lucide-react v0.487.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */
const W2 = [
  [
    "path",
    {
      d: "M2 20a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8l-7 5V8l-7 5V4a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z",
      key: "159hny"
    }
  ],
  ["path", { d: "M17 18h1", key: "uldtlt" }],
  ["path", { d: "M12 18h1", key: "s9uhes" }],
  ["path", { d: "M7 18h1", key: "1neino" }]
], H2 = ve("factory", W2);
/**
 * @license lucide-react v0.487.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */
const G2 = [
  [
    "path",
    {
      d: "M10 20a1 1 0 0 0 .553.895l2 1A1 1 0 0 0 14 21v-7a2 2 0 0 1 .517-1.341L21.74 4.67A1 1 0 0 0 21 3H3a1 1 0 0 0-.742 1.67l7.225 7.989A2 2 0 0 1 10 14z",
      key: "sc7q7i"
    }
  ]
], K2 = ve("funnel", G2);
/**
 * @license lucide-react v0.487.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */
const V2 = [
  ["circle", { cx: "12", cy: "12", r: "10", key: "1mglay" }],
  ["path", { d: "M12 16v-4", key: "1dtifu" }],
  ["path", { d: "M12 8h.01", key: "e9boi3" }]
], X2 = ve("info", V2);
/**
 * @license lucide-react v0.487.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */
const Y2 = [
  ["path", { d: "m15.5 7.5 2.3 2.3a1 1 0 0 0 1.4 0l2.1-2.1a1 1 0 0 0 0-1.4L19 4", key: "g0fldk" }],
  ["path", { d: "m21 2-9.6 9.6", key: "1j0ho8" }],
  ["circle", { cx: "7.5", cy: "15.5", r: "5.5", key: "yqb3hr" }]
], Z2 = ve("key", Y2);
/**
 * @license lucide-react v0.487.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */
const J2 = [
  ["rect", { width: "7", height: "9", x: "3", y: "3", rx: "1", key: "10lvy0" }],
  ["rect", { width: "7", height: "5", x: "14", y: "3", rx: "1", key: "16une8" }],
  ["rect", { width: "7", height: "9", x: "14", y: "12", rx: "1", key: "1hutg5" }],
  ["rect", { width: "7", height: "5", x: "3", y: "16", rx: "1", key: "ldoo1y" }]
], Q2 = ve("layout-dashboard", J2);
/**
 * @license lucide-react v0.487.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */
const eE = [
  ["path", { d: "M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4", key: "u53s6r" }],
  ["polyline", { points: "10 17 15 12 10 7", key: "1ail0h" }],
  ["line", { x1: "15", x2: "3", y1: "12", y2: "12", key: "v6grx8" }]
], tE = ve("log-in", eE);
/**
 * @license lucide-react v0.487.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */
const rE = [
  ["path", { d: "M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4", key: "1uf3rs" }],
  ["polyline", { points: "16 17 21 12 16 7", key: "1gabdz" }],
  ["line", { x1: "21", x2: "9", y1: "12", y2: "12", key: "1uyos4" }]
], ow = ve("log-out", rE);
/**
 * @license lucide-react v0.487.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */
const nE = [
  ["rect", { width: "20", height: "16", x: "2", y: "4", rx: "2", key: "18n3k1" }],
  ["path", { d: "m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7", key: "1ocrg3" }]
], aE = ve("mail", nE);
/**
 * @license lucide-react v0.487.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */
const iE = [
  [
    "path",
    {
      d: "M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0",
      key: "1r0f0z"
    }
  ],
  ["circle", { cx: "12", cy: "10", r: "3", key: "ilqhr7" }]
], oE = ve("map-pin", iE);
/**
 * @license lucide-react v0.487.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */
const uE = [
  ["line", { x1: "4", x2: "20", y1: "12", y2: "12", key: "1e0a9i" }],
  ["line", { x1: "4", x2: "20", y1: "6", y2: "6", key: "1owob3" }],
  ["line", { x1: "4", x2: "20", y1: "18", y2: "18", key: "yk5zj1" }]
], lE = ve("menu", uE);
/**
 * @license lucide-react v0.487.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */
const sE = [
  ["path", { d: "M12 20h9", key: "t2du7b" }],
  [
    "path",
    {
      d: "M16.376 3.622a1 1 0 0 1 3.002 3.002L7.368 18.635a2 2 0 0 1-.855.506l-2.872.838a.5.5 0 0 1-.62-.62l.838-2.872a2 2 0 0 1 .506-.854z",
      key: "1ykcvy"
    }
  ]
], cE = ve("pen-line", sE);
/**
 * @license lucide-react v0.487.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */
const fE = [
  [
    "path",
    {
      d: "M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z",
      key: "foiqr5"
    }
  ]
], dE = ve("phone", fE);
/**
 * @license lucide-react v0.487.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */
const hE = [
  ["path", { d: "M5 12h14", key: "1ays0h" }],
  ["path", { d: "M12 5v14", key: "s699le" }]
], pE = ve("plus", hE);
/**
 * @license lucide-react v0.487.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */
const vE = [
  [
    "path",
    {
      d: "M15.2 3a2 2 0 0 1 1.4.6l3.8 3.8a2 2 0 0 1 .6 1.4V19a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z",
      key: "1c8476"
    }
  ],
  ["path", { d: "M17 21v-7a1 1 0 0 0-1-1H8a1 1 0 0 0-1 1v7", key: "1ydtos" }],
  ["path", { d: "M7 3v4a1 1 0 0 0 1 1h7", key: "t51u73" }]
], yE = ve("save", vE);
/**
 * @license lucide-react v0.487.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */
const mE = [
  ["circle", { cx: "11", cy: "11", r: "8", key: "4ej97u" }],
  ["path", { d: "m21 21-4.3-4.3", key: "1qie3q" }]
], gE = ve("search", mE);
/**
 * @license lucide-react v0.487.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */
const bE = [
  [
    "path",
    {
      d: "M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z",
      key: "1qme2f"
    }
  ],
  ["circle", { cx: "12", cy: "12", r: "3", key: "1v7zrd" }]
], xE = ve("settings", bE);
/**
 * @license lucide-react v0.487.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */
const wE = [
  [
    "path",
    {
      d: "M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z",
      key: "oel41y"
    }
  ]
], nl = ve("shield", wE);
/**
 * @license lucide-react v0.487.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */
const OE = [
  ["circle", { cx: "12", cy: "12", r: "10", key: "1mglay" }],
  ["circle", { cx: "12", cy: "12", r: "6", key: "1vlfrh" }],
  ["circle", { cx: "12", cy: "12", r: "2", key: "1c9p78" }]
], _E = ve("target", OE);
/**
 * @license lucide-react v0.487.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */
const SE = [
  ["polyline", { points: "22 7 13.5 15.5 8.5 10.5 2 17", key: "126l90" }],
  ["polyline", { points: "16 7 22 7 22 13", key: "kwv8wd" }]
], ld = ve("trending-up", SE);
/**
 * @license lucide-react v0.487.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */
const PE = [
  ["path", { d: "M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2", key: "975kel" }],
  ["circle", { cx: "12", cy: "7", r: "4", key: "17ys0d" }]
], po = ve("user", PE);
/**
 * @license lucide-react v0.487.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */
const AE = [
  ["path", { d: "M18 6 6 18", key: "1bl5f8" }],
  ["path", { d: "m6 6 12 12", key: "d8bk6v" }]
], vo = ve("x", AE), EE = [
  { path: "/", label: "工作台", icon: Q2, end: !0 },
  { path: "/work-report", label: "用户报工", icon: iw },
  { path: "/piece-wage", label: "计件工资", icon: yu },
  { path: "/attendance", label: "打卡签到", icon: Fh },
  { path: "/profile", label: "个人资料", icon: po }
];
function TE() {
  const [e, t] = Oe(!0), [r, n] = Oe(!1), a = kh();
  return /* @__PURE__ */ k("div", { className: "flex h-screen bg-gray-50 overflow-hidden", children: [
    r && /* @__PURE__ */ P(
      "div",
      {
        className: "fixed inset-0 bg-black/40 z-30 md:hidden",
        onClick: () => n(!1)
      }
    ),
    /* @__PURE__ */ k(
      "aside",
      {
        className: `
          fixed md:relative z-40 flex flex-col bg-white border-r border-gray-200
          transition-all duration-300 h-full
          ${e ? "w-60" : "w-16"}
          ${r ? "translate-x-0" : "-translate-x-full md:translate-x-0"}
        `,
        children: [
          /* @__PURE__ */ k("div", { className: "flex items-center gap-3 px-4 py-5 border-b border-gray-100", children: [
            /* @__PURE__ */ P("div", { className: "flex-shrink-0 w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center", children: /* @__PURE__ */ P(H2, { size: 18, className: "text-white" }) }),
            e && /* @__PURE__ */ k("div", { className: "overflow-hidden", children: [
              /* @__PURE__ */ P("p", { className: "text-sm font-semibold text-gray-900 whitespace-nowrap", children: "MES 用户中心" }),
              /* @__PURE__ */ P("p", { className: "text-xs text-gray-400 whitespace-nowrap", children: "制造执行系统" })
            ] }),
            /* @__PURE__ */ P(
              "button",
              {
                className: "ml-auto hidden md:flex text-gray-400 hover:text-gray-600 flex-shrink-0",
                onClick: () => t(!e),
                children: /* @__PURE__ */ P(aw, { size: 16, className: `transition-transform ${e ? "rotate-180" : ""}` })
              }
            )
          ] }),
          /* @__PURE__ */ P("nav", { className: "flex-1 px-2 py-4 space-y-1 overflow-y-auto", children: EE.map((i) => /* @__PURE__ */ P(
            ew,
            {
              to: i.path,
              end: i.end,
              onClick: () => n(!1),
              className: ({ isActive: o }) => `flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group
                ${o ? "bg-blue-50 text-blue-600" : "text-gray-600 hover:bg-gray-50 hover:text-gray-900"}`,
              children: ({ isActive: o }) => /* @__PURE__ */ k(w1, { children: [
                /* @__PURE__ */ P(i.icon, { size: 20, className: `flex-shrink-0 ${o ? "text-blue-600" : "text-gray-400 group-hover:text-gray-600"}` }),
                e && /* @__PURE__ */ P("span", { className: "text-sm whitespace-nowrap", children: i.label })
              ] })
            },
            i.path
          )) }),
          e && /* @__PURE__ */ P("div", { className: "border-t border-gray-100 p-4", children: /* @__PURE__ */ k("div", { className: "flex items-center gap-3", children: [
            /* @__PURE__ */ P("div", { className: "w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center flex-shrink-0", children: /* @__PURE__ */ P("span", { className: "text-white text-xs font-semibold", children: "张" }) }),
            /* @__PURE__ */ k("div", { className: "flex-1 min-w-0", children: [
              /* @__PURE__ */ P("p", { className: "text-sm font-medium text-gray-900 truncate", children: "张伟" }),
              /* @__PURE__ */ P("p", { className: "text-xs text-gray-400 truncate", children: "装配车间 · 员工" })
            ] }),
            /* @__PURE__ */ P("button", { className: "text-gray-400 hover:text-red-500 transition-colors", onClick: () => a("/"), children: /* @__PURE__ */ P(ow, { size: 16 }) })
          ] }) })
        ]
      }
    ),
    /* @__PURE__ */ k("div", { className: "flex-1 flex flex-col min-w-0 overflow-hidden", children: [
      /* @__PURE__ */ k("header", { className: "bg-white border-b border-gray-200 px-4 md:px-6 py-3 flex items-center gap-4 flex-shrink-0", children: [
        /* @__PURE__ */ P(
          "button",
          {
            className: "md:hidden text-gray-500 hover:text-gray-700",
            onClick: () => n(!0),
            children: /* @__PURE__ */ P(lE, { size: 22 })
          }
        ),
        /* @__PURE__ */ P("div", { className: "flex-1" }),
        /* @__PURE__ */ k("button", { className: "relative p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-50 rounded-lg transition-colors", children: [
          /* @__PURE__ */ P(A2, { size: 20 }),
          /* @__PURE__ */ P("span", { className: "absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full" })
        ] }),
        /* @__PURE__ */ P("button", { className: "p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-50 rounded-lg transition-colors", children: /* @__PURE__ */ P(xE, { size: 20 }) }),
        /* @__PURE__ */ P("div", { className: "w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center cursor-pointer", onClick: () => a("/profile"), children: /* @__PURE__ */ P("span", { className: "text-white text-xs font-semibold", children: "张" }) })
      ] }),
      /* @__PURE__ */ P("main", { className: "flex-1 overflow-y-auto", children: /* @__PURE__ */ P($A, {}) })
    ] })
  ] });
}
function uw(e) {
  var t, r, n = "";
  if (typeof e == "string" || typeof e == "number") n += e;
  else if (typeof e == "object") if (Array.isArray(e)) {
    var a = e.length;
    for (t = 0; t < a; t++) e[t] && (r = uw(e[t])) && (n && (n += " "), n += r);
  } else for (r in e) e[r] && (n && (n += " "), n += r);
  return n;
}
function _e() {
  for (var e, t, r = 0, n = "", a = arguments.length; r < a; r++) (e = arguments[r]) && (t = uw(e)) && (n && (n += " "), n += t);
  return n;
}
var al, $v;
function ht() {
  if ($v) return al;
  $v = 1;
  var e = Array.isArray;
  return al = e, al;
}
var il, Rv;
function lw() {
  if (Rv) return il;
  Rv = 1;
  var e = typeof Xi == "object" && Xi && Xi.Object === Object && Xi;
  return il = e, il;
}
var ol, kv;
function Qt() {
  if (kv) return ol;
  kv = 1;
  var e = lw(), t = typeof self == "object" && self && self.Object === Object && self, r = e || t || Function("return this")();
  return ol = r, ol;
}
var ul, Iv;
function Ni() {
  if (Iv) return ul;
  Iv = 1;
  var e = Qt(), t = e.Symbol;
  return ul = t, ul;
}
var ll, Dv;
function ME() {
  if (Dv) return ll;
  Dv = 1;
  var e = Ni(), t = Object.prototype, r = t.hasOwnProperty, n = t.toString, a = e ? e.toStringTag : void 0;
  function i(o) {
    var u = r.call(o, a), l = o[a];
    try {
      o[a] = void 0;
      var s = !0;
    } catch {
    }
    var f = n.call(o);
    return s && (u ? o[a] = l : delete o[a]), f;
  }
  return ll = i, ll;
}
var sl, Lv;
function jE() {
  if (Lv) return sl;
  Lv = 1;
  var e = Object.prototype, t = e.toString;
  function r(n) {
    return t.call(n);
  }
  return sl = r, sl;
}
var cl, qv;
function xr() {
  if (qv) return cl;
  qv = 1;
  var e = Ni(), t = ME(), r = jE(), n = "[object Null]", a = "[object Undefined]", i = e ? e.toStringTag : void 0;
  function o(u) {
    return u == null ? u === void 0 ? a : n : i && i in Object(u) ? t(u) : r(u);
  }
  return cl = o, cl;
}
var fl, Bv;
function wr() {
  if (Bv) return fl;
  Bv = 1;
  function e(t) {
    return t != null && typeof t == "object";
  }
  return fl = e, fl;
}
var dl, Fv;
function na() {
  if (Fv) return dl;
  Fv = 1;
  var e = xr(), t = wr(), r = "[object Symbol]";
  function n(a) {
    return typeof a == "symbol" || t(a) && e(a) == r;
  }
  return dl = n, dl;
}
var hl, zv;
function zh() {
  if (zv) return hl;
  zv = 1;
  var e = ht(), t = na(), r = /\.|\[(?:[^[\]]*|(["'])(?:(?!\1)[^\\]|\\.)*?\1)\]/, n = /^\w*$/;
  function a(i, o) {
    if (e(i))
      return !1;
    var u = typeof i;
    return u == "number" || u == "symbol" || u == "boolean" || i == null || t(i) ? !0 : n.test(i) || !r.test(i) || o != null && i in Object(o);
  }
  return hl = a, hl;
}
var pl, Uv;
function Lr() {
  if (Uv) return pl;
  Uv = 1;
  function e(t) {
    var r = typeof t;
    return t != null && (r == "object" || r == "function");
  }
  return pl = e, pl;
}
var vl, Wv;
function Uh() {
  if (Wv) return vl;
  Wv = 1;
  var e = xr(), t = Lr(), r = "[object AsyncFunction]", n = "[object Function]", a = "[object GeneratorFunction]", i = "[object Proxy]";
  function o(u) {
    if (!t(u))
      return !1;
    var l = e(u);
    return l == n || l == a || l == r || l == i;
  }
  return vl = o, vl;
}
var yl, Hv;
function NE() {
  if (Hv) return yl;
  Hv = 1;
  var e = Qt(), t = e["__core-js_shared__"];
  return yl = t, yl;
}
var ml, Gv;
function CE() {
  if (Gv) return ml;
  Gv = 1;
  var e = NE(), t = (function() {
    var n = /[^.]+$/.exec(e && e.keys && e.keys.IE_PROTO || "");
    return n ? "Symbol(src)_1." + n : "";
  })();
  function r(n) {
    return !!t && t in n;
  }
  return ml = r, ml;
}
var gl, Kv;
function sw() {
  if (Kv) return gl;
  Kv = 1;
  var e = Function.prototype, t = e.toString;
  function r(n) {
    if (n != null) {
      try {
        return t.call(n);
      } catch {
      }
      try {
        return n + "";
      } catch {
      }
    }
    return "";
  }
  return gl = r, gl;
}
var bl, Vv;
function $E() {
  if (Vv) return bl;
  Vv = 1;
  var e = Uh(), t = CE(), r = Lr(), n = sw(), a = /[\\^$.*+?()[\]{}|]/g, i = /^\[object .+?Constructor\]$/, o = Function.prototype, u = Object.prototype, l = o.toString, s = u.hasOwnProperty, f = RegExp(
    "^" + l.call(s).replace(a, "\\$&").replace(/hasOwnProperty|(function).*?(?=\\\()| for .+?(?=\\\])/g, "$1.*?") + "$"
  );
  function c(d) {
    if (!r(d) || t(d))
      return !1;
    var h = e(d) ? f : i;
    return h.test(n(d));
  }
  return bl = c, bl;
}
var xl, Xv;
function RE() {
  if (Xv) return xl;
  Xv = 1;
  function e(t, r) {
    return t?.[r];
  }
  return xl = e, xl;
}
var wl, Yv;
function vn() {
  if (Yv) return wl;
  Yv = 1;
  var e = $E(), t = RE();
  function r(n, a) {
    var i = t(n, a);
    return e(i) ? i : void 0;
  }
  return wl = r, wl;
}
var Ol, Zv;
function mu() {
  if (Zv) return Ol;
  Zv = 1;
  var e = vn(), t = e(Object, "create");
  return Ol = t, Ol;
}
var _l, Jv;
function kE() {
  if (Jv) return _l;
  Jv = 1;
  var e = mu();
  function t() {
    this.__data__ = e ? e(null) : {}, this.size = 0;
  }
  return _l = t, _l;
}
var Sl, Qv;
function IE() {
  if (Qv) return Sl;
  Qv = 1;
  function e(t) {
    var r = this.has(t) && delete this.__data__[t];
    return this.size -= r ? 1 : 0, r;
  }
  return Sl = e, Sl;
}
var Pl, ey;
function DE() {
  if (ey) return Pl;
  ey = 1;
  var e = mu(), t = "__lodash_hash_undefined__", r = Object.prototype, n = r.hasOwnProperty;
  function a(i) {
    var o = this.__data__;
    if (e) {
      var u = o[i];
      return u === t ? void 0 : u;
    }
    return n.call(o, i) ? o[i] : void 0;
  }
  return Pl = a, Pl;
}
var Al, ty;
function LE() {
  if (ty) return Al;
  ty = 1;
  var e = mu(), t = Object.prototype, r = t.hasOwnProperty;
  function n(a) {
    var i = this.__data__;
    return e ? i[a] !== void 0 : r.call(i, a);
  }
  return Al = n, Al;
}
var El, ry;
function qE() {
  if (ry) return El;
  ry = 1;
  var e = mu(), t = "__lodash_hash_undefined__";
  function r(n, a) {
    var i = this.__data__;
    return this.size += this.has(n) ? 0 : 1, i[n] = e && a === void 0 ? t : a, this;
  }
  return El = r, El;
}
var Tl, ny;
function BE() {
  if (ny) return Tl;
  ny = 1;
  var e = kE(), t = IE(), r = DE(), n = LE(), a = qE();
  function i(o) {
    var u = -1, l = o == null ? 0 : o.length;
    for (this.clear(); ++u < l; ) {
      var s = o[u];
      this.set(s[0], s[1]);
    }
  }
  return i.prototype.clear = e, i.prototype.delete = t, i.prototype.get = r, i.prototype.has = n, i.prototype.set = a, Tl = i, Tl;
}
var Ml, ay;
function FE() {
  if (ay) return Ml;
  ay = 1;
  function e() {
    this.__data__ = [], this.size = 0;
  }
  return Ml = e, Ml;
}
var jl, iy;
function Wh() {
  if (iy) return jl;
  iy = 1;
  function e(t, r) {
    return t === r || t !== t && r !== r;
  }
  return jl = e, jl;
}
var Nl, oy;
function gu() {
  if (oy) return Nl;
  oy = 1;
  var e = Wh();
  function t(r, n) {
    for (var a = r.length; a--; )
      if (e(r[a][0], n))
        return a;
    return -1;
  }
  return Nl = t, Nl;
}
var Cl, uy;
function zE() {
  if (uy) return Cl;
  uy = 1;
  var e = gu(), t = Array.prototype, r = t.splice;
  function n(a) {
    var i = this.__data__, o = e(i, a);
    if (o < 0)
      return !1;
    var u = i.length - 1;
    return o == u ? i.pop() : r.call(i, o, 1), --this.size, !0;
  }
  return Cl = n, Cl;
}
var $l, ly;
function UE() {
  if (ly) return $l;
  ly = 1;
  var e = gu();
  function t(r) {
    var n = this.__data__, a = e(n, r);
    return a < 0 ? void 0 : n[a][1];
  }
  return $l = t, $l;
}
var Rl, sy;
function WE() {
  if (sy) return Rl;
  sy = 1;
  var e = gu();
  function t(r) {
    return e(this.__data__, r) > -1;
  }
  return Rl = t, Rl;
}
var kl, cy;
function HE() {
  if (cy) return kl;
  cy = 1;
  var e = gu();
  function t(r, n) {
    var a = this.__data__, i = e(a, r);
    return i < 0 ? (++this.size, a.push([r, n])) : a[i][1] = n, this;
  }
  return kl = t, kl;
}
var Il, fy;
function bu() {
  if (fy) return Il;
  fy = 1;
  var e = FE(), t = zE(), r = UE(), n = WE(), a = HE();
  function i(o) {
    var u = -1, l = o == null ? 0 : o.length;
    for (this.clear(); ++u < l; ) {
      var s = o[u];
      this.set(s[0], s[1]);
    }
  }
  return i.prototype.clear = e, i.prototype.delete = t, i.prototype.get = r, i.prototype.has = n, i.prototype.set = a, Il = i, Il;
}
var Dl, dy;
function Hh() {
  if (dy) return Dl;
  dy = 1;
  var e = vn(), t = Qt(), r = e(t, "Map");
  return Dl = r, Dl;
}
var Ll, hy;
function GE() {
  if (hy) return Ll;
  hy = 1;
  var e = BE(), t = bu(), r = Hh();
  function n() {
    this.size = 0, this.__data__ = {
      hash: new e(),
      map: new (r || t)(),
      string: new e()
    };
  }
  return Ll = n, Ll;
}
var ql, py;
function KE() {
  if (py) return ql;
  py = 1;
  function e(t) {
    var r = typeof t;
    return r == "string" || r == "number" || r == "symbol" || r == "boolean" ? t !== "__proto__" : t === null;
  }
  return ql = e, ql;
}
var Bl, vy;
function xu() {
  if (vy) return Bl;
  vy = 1;
  var e = KE();
  function t(r, n) {
    var a = r.__data__;
    return e(n) ? a[typeof n == "string" ? "string" : "hash"] : a.map;
  }
  return Bl = t, Bl;
}
var Fl, yy;
function VE() {
  if (yy) return Fl;
  yy = 1;
  var e = xu();
  function t(r) {
    var n = e(this, r).delete(r);
    return this.size -= n ? 1 : 0, n;
  }
  return Fl = t, Fl;
}
var zl, my;
function XE() {
  if (my) return zl;
  my = 1;
  var e = xu();
  function t(r) {
    return e(this, r).get(r);
  }
  return zl = t, zl;
}
var Ul, gy;
function YE() {
  if (gy) return Ul;
  gy = 1;
  var e = xu();
  function t(r) {
    return e(this, r).has(r);
  }
  return Ul = t, Ul;
}
var Wl, by;
function ZE() {
  if (by) return Wl;
  by = 1;
  var e = xu();
  function t(r, n) {
    var a = e(this, r), i = a.size;
    return a.set(r, n), this.size += a.size == i ? 0 : 1, this;
  }
  return Wl = t, Wl;
}
var Hl, xy;
function Gh() {
  if (xy) return Hl;
  xy = 1;
  var e = GE(), t = VE(), r = XE(), n = YE(), a = ZE();
  function i(o) {
    var u = -1, l = o == null ? 0 : o.length;
    for (this.clear(); ++u < l; ) {
      var s = o[u];
      this.set(s[0], s[1]);
    }
  }
  return i.prototype.clear = e, i.prototype.delete = t, i.prototype.get = r, i.prototype.has = n, i.prototype.set = a, Hl = i, Hl;
}
var Gl, wy;
function cw() {
  if (wy) return Gl;
  wy = 1;
  var e = Gh(), t = "Expected a function";
  function r(n, a) {
    if (typeof n != "function" || a != null && typeof a != "function")
      throw new TypeError(t);
    var i = function() {
      var o = arguments, u = a ? a.apply(this, o) : o[0], l = i.cache;
      if (l.has(u))
        return l.get(u);
      var s = n.apply(this, o);
      return i.cache = l.set(u, s) || l, s;
    };
    return i.cache = new (r.Cache || e)(), i;
  }
  return r.Cache = e, Gl = r, Gl;
}
var Kl, Oy;
function JE() {
  if (Oy) return Kl;
  Oy = 1;
  var e = cw(), t = 500;
  function r(n) {
    var a = e(n, function(o) {
      return i.size === t && i.clear(), o;
    }), i = a.cache;
    return a;
  }
  return Kl = r, Kl;
}
var Vl, _y;
function QE() {
  if (_y) return Vl;
  _y = 1;
  var e = JE(), t = /[^.[\]]+|\[(?:(-?\d+(?:\.\d+)?)|(["'])((?:(?!\2)[^\\]|\\.)*?)\2)\]|(?=(?:\.|\[\])(?:\.|\[\]|$))/g, r = /\\(\\)?/g, n = e(function(a) {
    var i = [];
    return a.charCodeAt(0) === 46 && i.push(""), a.replace(t, function(o, u, l, s) {
      i.push(l ? s.replace(r, "$1") : u || o);
    }), i;
  });
  return Vl = n, Vl;
}
var Xl, Sy;
function Kh() {
  if (Sy) return Xl;
  Sy = 1;
  function e(t, r) {
    for (var n = -1, a = t == null ? 0 : t.length, i = Array(a); ++n < a; )
      i[n] = r(t[n], n, t);
    return i;
  }
  return Xl = e, Xl;
}
var Yl, Py;
function eT() {
  if (Py) return Yl;
  Py = 1;
  var e = Ni(), t = Kh(), r = ht(), n = na(), a = e ? e.prototype : void 0, i = a ? a.toString : void 0;
  function o(u) {
    if (typeof u == "string")
      return u;
    if (r(u))
      return t(u, o) + "";
    if (n(u))
      return i ? i.call(u) : "";
    var l = u + "";
    return l == "0" && 1 / u == -1 / 0 ? "-0" : l;
  }
  return Yl = o, Yl;
}
var Zl, Ay;
function fw() {
  if (Ay) return Zl;
  Ay = 1;
  var e = eT();
  function t(r) {
    return r == null ? "" : e(r);
  }
  return Zl = t, Zl;
}
var Jl, Ey;
function dw() {
  if (Ey) return Jl;
  Ey = 1;
  var e = ht(), t = zh(), r = QE(), n = fw();
  function a(i, o) {
    return e(i) ? i : t(i, o) ? [i] : r(n(i));
  }
  return Jl = a, Jl;
}
var Ql, Ty;
function wu() {
  if (Ty) return Ql;
  Ty = 1;
  var e = na();
  function t(r) {
    if (typeof r == "string" || e(r))
      return r;
    var n = r + "";
    return n == "0" && 1 / r == -1 / 0 ? "-0" : n;
  }
  return Ql = t, Ql;
}
var es, My;
function Vh() {
  if (My) return es;
  My = 1;
  var e = dw(), t = wu();
  function r(n, a) {
    a = e(a, n);
    for (var i = 0, o = a.length; n != null && i < o; )
      n = n[t(a[i++])];
    return i && i == o ? n : void 0;
  }
  return es = r, es;
}
var ts, jy;
function hw() {
  if (jy) return ts;
  jy = 1;
  var e = Vh();
  function t(r, n, a) {
    var i = r == null ? void 0 : e(r, n);
    return i === void 0 ? a : i;
  }
  return ts = t, ts;
}
var tT = hw();
const Tt = /* @__PURE__ */ $e(tT);
var rs, Ny;
function rT() {
  if (Ny) return rs;
  Ny = 1;
  function e(t) {
    return t == null;
  }
  return rs = e, rs;
}
var nT = rT();
const me = /* @__PURE__ */ $e(nT);
var ns, Cy;
function aT() {
  if (Cy) return ns;
  Cy = 1;
  var e = xr(), t = ht(), r = wr(), n = "[object String]";
  function a(i) {
    return typeof i == "string" || !t(i) && r(i) && e(i) == n;
  }
  return ns = a, ns;
}
var iT = aT();
const Ci = /* @__PURE__ */ $e(iT);
var oT = Uh();
const fe = /* @__PURE__ */ $e(oT);
var uT = Lr();
const aa = /* @__PURE__ */ $e(uT);
var as = { exports: {} }, Pe = {};
/**
 * @license React
 * react-is.production.min.js
 *
 * Copyright (c) Facebook, Inc. and its affiliates.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */
var $y;
function lT() {
  if ($y) return Pe;
  $y = 1;
  var e = Symbol.for("react.element"), t = Symbol.for("react.portal"), r = Symbol.for("react.fragment"), n = Symbol.for("react.strict_mode"), a = Symbol.for("react.profiler"), i = Symbol.for("react.provider"), o = Symbol.for("react.context"), u = Symbol.for("react.server_context"), l = Symbol.for("react.forward_ref"), s = Symbol.for("react.suspense"), f = Symbol.for("react.suspense_list"), c = Symbol.for("react.memo"), d = Symbol.for("react.lazy"), h = Symbol.for("react.offscreen"), y;
  y = Symbol.for("react.module.reference");
  function v(p) {
    if (typeof p == "object" && p !== null) {
      var g = p.$$typeof;
      switch (g) {
        case e:
          switch (p = p.type, p) {
            case r:
            case a:
            case n:
            case s:
            case f:
              return p;
            default:
              switch (p = p && p.$$typeof, p) {
                case u:
                case o:
                case l:
                case d:
                case c:
                case i:
                  return p;
                default:
                  return g;
              }
          }
        case t:
          return g;
      }
    }
  }
  return Pe.ContextConsumer = o, Pe.ContextProvider = i, Pe.Element = e, Pe.ForwardRef = l, Pe.Fragment = r, Pe.Lazy = d, Pe.Memo = c, Pe.Portal = t, Pe.Profiler = a, Pe.StrictMode = n, Pe.Suspense = s, Pe.SuspenseList = f, Pe.isAsyncMode = function() {
    return !1;
  }, Pe.isConcurrentMode = function() {
    return !1;
  }, Pe.isContextConsumer = function(p) {
    return v(p) === o;
  }, Pe.isContextProvider = function(p) {
    return v(p) === i;
  }, Pe.isElement = function(p) {
    return typeof p == "object" && p !== null && p.$$typeof === e;
  }, Pe.isForwardRef = function(p) {
    return v(p) === l;
  }, Pe.isFragment = function(p) {
    return v(p) === r;
  }, Pe.isLazy = function(p) {
    return v(p) === d;
  }, Pe.isMemo = function(p) {
    return v(p) === c;
  }, Pe.isPortal = function(p) {
    return v(p) === t;
  }, Pe.isProfiler = function(p) {
    return v(p) === a;
  }, Pe.isStrictMode = function(p) {
    return v(p) === n;
  }, Pe.isSuspense = function(p) {
    return v(p) === s;
  }, Pe.isSuspenseList = function(p) {
    return v(p) === f;
  }, Pe.isValidElementType = function(p) {
    return typeof p == "string" || typeof p == "function" || p === r || p === a || p === n || p === s || p === f || p === h || typeof p == "object" && p !== null && (p.$$typeof === d || p.$$typeof === c || p.$$typeof === i || p.$$typeof === o || p.$$typeof === l || p.$$typeof === y || p.getModuleId !== void 0);
  }, Pe.typeOf = v, Pe;
}
var Ry;
function sT() {
  return Ry || (Ry = 1, as.exports = lT()), as.exports;
}
var cT = sT(), is, ky;
function pw() {
  if (ky) return is;
  ky = 1;
  var e = xr(), t = wr(), r = "[object Number]";
  function n(a) {
    return typeof a == "number" || t(a) && e(a) == r;
  }
  return is = n, is;
}
var os, Iy;
function fT() {
  if (Iy) return os;
  Iy = 1;
  var e = pw();
  function t(r) {
    return e(r) && r != +r;
  }
  return os = t, os;
}
var dT = fT();
const ia = /* @__PURE__ */ $e(dT);
var hT = pw();
const pT = /* @__PURE__ */ $e(hT);
var Dt = function(t) {
  return t === 0 ? 0 : t > 0 ? 1 : -1;
}, Zr = function(t) {
  return Ci(t) && t.indexOf("%") === t.length - 1;
}, H = function(t) {
  return pT(t) && !ia(t);
}, Ve = function(t) {
  return H(t) || Ci(t);
}, vT = 0, $i = function(t) {
  var r = ++vT;
  return "".concat(t || "").concat(r);
}, on = function(t, r) {
  var n = arguments.length > 2 && arguments[2] !== void 0 ? arguments[2] : 0, a = arguments.length > 3 && arguments[3] !== void 0 ? arguments[3] : !1;
  if (!H(t) && !Ci(t))
    return n;
  var i;
  if (Zr(t)) {
    var o = t.indexOf("%");
    i = r * parseFloat(t.slice(0, o)) / 100;
  } else
    i = +t;
  return ia(i) && (i = n), a && i > r && (i = r), i;
}, Mr = function(t) {
  if (!t)
    return null;
  var r = Object.keys(t);
  return r && r.length ? t[r[0]] : null;
}, yT = function(t) {
  if (!Array.isArray(t))
    return !1;
  for (var r = t.length, n = {}, a = 0; a < r; a++)
    if (!n[t[a]])
      n[t[a]] = !0;
    else
      return !0;
  return !1;
}, At = function(t, r) {
  return H(t) && H(r) ? function(n) {
    return t + n * (r - t);
  } : function() {
    return r;
  };
};
function yo(e, t, r) {
  return !e || !e.length ? null : e.find(function(n) {
    return n && (typeof t == "function" ? t(n) : Tt(n, t)) === r;
  });
}
function Nn(e, t) {
  for (var r in e)
    if ({}.hasOwnProperty.call(e, r) && (!{}.hasOwnProperty.call(t, r) || e[r] !== t[r]))
      return !1;
  for (var n in t)
    if ({}.hasOwnProperty.call(t, n) && !{}.hasOwnProperty.call(e, n))
      return !1;
  return !0;
}
function sd(e) {
  "@babel/helpers - typeof";
  return sd = typeof Symbol == "function" && typeof Symbol.iterator == "symbol" ? function(t) {
    return typeof t;
  } : function(t) {
    return t && typeof Symbol == "function" && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t;
  }, sd(e);
}
var mT = ["viewBox", "children"], gT = [
  "aria-activedescendant",
  "aria-atomic",
  "aria-autocomplete",
  "aria-busy",
  "aria-checked",
  "aria-colcount",
  "aria-colindex",
  "aria-colspan",
  "aria-controls",
  "aria-current",
  "aria-describedby",
  "aria-details",
  "aria-disabled",
  "aria-errormessage",
  "aria-expanded",
  "aria-flowto",
  "aria-haspopup",
  "aria-hidden",
  "aria-invalid",
  "aria-keyshortcuts",
  "aria-label",
  "aria-labelledby",
  "aria-level",
  "aria-live",
  "aria-modal",
  "aria-multiline",
  "aria-multiselectable",
  "aria-orientation",
  "aria-owns",
  "aria-placeholder",
  "aria-posinset",
  "aria-pressed",
  "aria-readonly",
  "aria-relevant",
  "aria-required",
  "aria-roledescription",
  "aria-rowcount",
  "aria-rowindex",
  "aria-rowspan",
  "aria-selected",
  "aria-setsize",
  "aria-sort",
  "aria-valuemax",
  "aria-valuemin",
  "aria-valuenow",
  "aria-valuetext",
  "className",
  "color",
  "height",
  "id",
  "lang",
  "max",
  "media",
  "method",
  "min",
  "name",
  "style",
  /*
   * removed 'type' SVGElementPropKey because we do not currently use any SVG elements
   * that can use it and it conflicts with the recharts prop 'type'
   * https://github.com/recharts/recharts/pull/3327
   * https://developer.mozilla.org/en-US/docs/Web/SVG/Attribute/type
   */
  // 'type',
  "target",
  "width",
  "role",
  "tabIndex",
  "accentHeight",
  "accumulate",
  "additive",
  "alignmentBaseline",
  "allowReorder",
  "alphabetic",
  "amplitude",
  "arabicForm",
  "ascent",
  "attributeName",
  "attributeType",
  "autoReverse",
  "azimuth",
  "baseFrequency",
  "baselineShift",
  "baseProfile",
  "bbox",
  "begin",
  "bias",
  "by",
  "calcMode",
  "capHeight",
  "clip",
  "clipPath",
  "clipPathUnits",
  "clipRule",
  "colorInterpolation",
  "colorInterpolationFilters",
  "colorProfile",
  "colorRendering",
  "contentScriptType",
  "contentStyleType",
  "cursor",
  "cx",
  "cy",
  "d",
  "decelerate",
  "descent",
  "diffuseConstant",
  "direction",
  "display",
  "divisor",
  "dominantBaseline",
  "dur",
  "dx",
  "dy",
  "edgeMode",
  "elevation",
  "enableBackground",
  "end",
  "exponent",
  "externalResourcesRequired",
  "fill",
  "fillOpacity",
  "fillRule",
  "filter",
  "filterRes",
  "filterUnits",
  "floodColor",
  "floodOpacity",
  "focusable",
  "fontFamily",
  "fontSize",
  "fontSizeAdjust",
  "fontStretch",
  "fontStyle",
  "fontVariant",
  "fontWeight",
  "format",
  "from",
  "fx",
  "fy",
  "g1",
  "g2",
  "glyphName",
  "glyphOrientationHorizontal",
  "glyphOrientationVertical",
  "glyphRef",
  "gradientTransform",
  "gradientUnits",
  "hanging",
  "horizAdvX",
  "horizOriginX",
  "href",
  "ideographic",
  "imageRendering",
  "in2",
  "in",
  "intercept",
  "k1",
  "k2",
  "k3",
  "k4",
  "k",
  "kernelMatrix",
  "kernelUnitLength",
  "kerning",
  "keyPoints",
  "keySplines",
  "keyTimes",
  "lengthAdjust",
  "letterSpacing",
  "lightingColor",
  "limitingConeAngle",
  "local",
  "markerEnd",
  "markerHeight",
  "markerMid",
  "markerStart",
  "markerUnits",
  "markerWidth",
  "mask",
  "maskContentUnits",
  "maskUnits",
  "mathematical",
  "mode",
  "numOctaves",
  "offset",
  "opacity",
  "operator",
  "order",
  "orient",
  "orientation",
  "origin",
  "overflow",
  "overlinePosition",
  "overlineThickness",
  "paintOrder",
  "panose1",
  "pathLength",
  "patternContentUnits",
  "patternTransform",
  "patternUnits",
  "pointerEvents",
  "pointsAtX",
  "pointsAtY",
  "pointsAtZ",
  "preserveAlpha",
  "preserveAspectRatio",
  "primitiveUnits",
  "r",
  "radius",
  "refX",
  "refY",
  "renderingIntent",
  "repeatCount",
  "repeatDur",
  "requiredExtensions",
  "requiredFeatures",
  "restart",
  "result",
  "rotate",
  "rx",
  "ry",
  "seed",
  "shapeRendering",
  "slope",
  "spacing",
  "specularConstant",
  "specularExponent",
  "speed",
  "spreadMethod",
  "startOffset",
  "stdDeviation",
  "stemh",
  "stemv",
  "stitchTiles",
  "stopColor",
  "stopOpacity",
  "strikethroughPosition",
  "strikethroughThickness",
  "string",
  "stroke",
  "strokeDasharray",
  "strokeDashoffset",
  "strokeLinecap",
  "strokeLinejoin",
  "strokeMiterlimit",
  "strokeOpacity",
  "strokeWidth",
  "surfaceScale",
  "systemLanguage",
  "tableValues",
  "targetX",
  "targetY",
  "textAnchor",
  "textDecoration",
  "textLength",
  "textRendering",
  "to",
  "transform",
  "u1",
  "u2",
  "underlinePosition",
  "underlineThickness",
  "unicode",
  "unicodeBidi",
  "unicodeRange",
  "unitsPerEm",
  "vAlphabetic",
  "values",
  "vectorEffect",
  "version",
  "vertAdvY",
  "vertOriginX",
  "vertOriginY",
  "vHanging",
  "vIdeographic",
  "viewTarget",
  "visibility",
  "vMathematical",
  "widths",
  "wordSpacing",
  "writingMode",
  "x1",
  "x2",
  "x",
  "xChannelSelector",
  "xHeight",
  "xlinkActuate",
  "xlinkArcrole",
  "xlinkHref",
  "xlinkRole",
  "xlinkShow",
  "xlinkTitle",
  "xlinkType",
  "xmlBase",
  "xmlLang",
  "xmlns",
  "xmlnsXlink",
  "xmlSpace",
  "y1",
  "y2",
  "y",
  "yChannelSelector",
  "z",
  "zoomAndPan",
  "ref",
  "key",
  "angle"
], Dy = ["points", "pathLength"], us = {
  svg: mT,
  polygon: Dy,
  polyline: Dy
}, Xh = ["dangerouslySetInnerHTML", "onCopy", "onCopyCapture", "onCut", "onCutCapture", "onPaste", "onPasteCapture", "onCompositionEnd", "onCompositionEndCapture", "onCompositionStart", "onCompositionStartCapture", "onCompositionUpdate", "onCompositionUpdateCapture", "onFocus", "onFocusCapture", "onBlur", "onBlurCapture", "onChange", "onChangeCapture", "onBeforeInput", "onBeforeInputCapture", "onInput", "onInputCapture", "onReset", "onResetCapture", "onSubmit", "onSubmitCapture", "onInvalid", "onInvalidCapture", "onLoad", "onLoadCapture", "onError", "onErrorCapture", "onKeyDown", "onKeyDownCapture", "onKeyPress", "onKeyPressCapture", "onKeyUp", "onKeyUpCapture", "onAbort", "onAbortCapture", "onCanPlay", "onCanPlayCapture", "onCanPlayThrough", "onCanPlayThroughCapture", "onDurationChange", "onDurationChangeCapture", "onEmptied", "onEmptiedCapture", "onEncrypted", "onEncryptedCapture", "onEnded", "onEndedCapture", "onLoadedData", "onLoadedDataCapture", "onLoadedMetadata", "onLoadedMetadataCapture", "onLoadStart", "onLoadStartCapture", "onPause", "onPauseCapture", "onPlay", "onPlayCapture", "onPlaying", "onPlayingCapture", "onProgress", "onProgressCapture", "onRateChange", "onRateChangeCapture", "onSeeked", "onSeekedCapture", "onSeeking", "onSeekingCapture", "onStalled", "onStalledCapture", "onSuspend", "onSuspendCapture", "onTimeUpdate", "onTimeUpdateCapture", "onVolumeChange", "onVolumeChangeCapture", "onWaiting", "onWaitingCapture", "onAuxClick", "onAuxClickCapture", "onClick", "onClickCapture", "onContextMenu", "onContextMenuCapture", "onDoubleClick", "onDoubleClickCapture", "onDrag", "onDragCapture", "onDragEnd", "onDragEndCapture", "onDragEnter", "onDragEnterCapture", "onDragExit", "onDragExitCapture", "onDragLeave", "onDragLeaveCapture", "onDragOver", "onDragOverCapture", "onDragStart", "onDragStartCapture", "onDrop", "onDropCapture", "onMouseDown", "onMouseDownCapture", "onMouseEnter", "onMouseLeave", "onMouseMove", "onMouseMoveCapture", "onMouseOut", "onMouseOutCapture", "onMouseOver", "onMouseOverCapture", "onMouseUp", "onMouseUpCapture", "onSelect", "onSelectCapture", "onTouchCancel", "onTouchCancelCapture", "onTouchEnd", "onTouchEndCapture", "onTouchMove", "onTouchMoveCapture", "onTouchStart", "onTouchStartCapture", "onPointerDown", "onPointerDownCapture", "onPointerMove", "onPointerMoveCapture", "onPointerUp", "onPointerUpCapture", "onPointerCancel", "onPointerCancelCapture", "onPointerEnter", "onPointerEnterCapture", "onPointerLeave", "onPointerLeaveCapture", "onPointerOver", "onPointerOverCapture", "onPointerOut", "onPointerOutCapture", "onGotPointerCapture", "onGotPointerCaptureCapture", "onLostPointerCapture", "onLostPointerCaptureCapture", "onScroll", "onScrollCapture", "onWheel", "onWheelCapture", "onAnimationStart", "onAnimationStartCapture", "onAnimationEnd", "onAnimationEndCapture", "onAnimationIteration", "onAnimationIterationCapture", "onTransitionEnd", "onTransitionEndCapture"], mo = function(t, r) {
  if (!t || typeof t == "function" || typeof t == "boolean")
    return null;
  var n = t;
  if (/* @__PURE__ */ Lt(t) && (n = t.props), !aa(n))
    return null;
  var a = {};
  return Object.keys(n).forEach(function(i) {
    Xh.includes(i) && (a[i] = r || function(o) {
      return n[i](n, o);
    });
  }), a;
}, bT = function(t, r, n) {
  return function(a) {
    return t(r, n, a), null;
  };
}, go = function(t, r, n) {
  if (!aa(t) || sd(t) !== "object")
    return null;
  var a = null;
  return Object.keys(t).forEach(function(i) {
    var o = t[i];
    Xh.includes(i) && typeof o == "function" && (a || (a = {}), a[i] = bT(o, r, n));
  }), a;
}, xT = ["children"], wT = ["children"];
function Ly(e, t) {
  if (e == null) return {};
  var r = OT(e, t), n, a;
  if (Object.getOwnPropertySymbols) {
    var i = Object.getOwnPropertySymbols(e);
    for (a = 0; a < i.length; a++)
      n = i[a], !(t.indexOf(n) >= 0) && Object.prototype.propertyIsEnumerable.call(e, n) && (r[n] = e[n]);
  }
  return r;
}
function OT(e, t) {
  if (e == null) return {};
  var r = {};
  for (var n in e)
    if (Object.prototype.hasOwnProperty.call(e, n)) {
      if (t.indexOf(n) >= 0) continue;
      r[n] = e[n];
    }
  return r;
}
function cd(e) {
  "@babel/helpers - typeof";
  return cd = typeof Symbol == "function" && typeof Symbol.iterator == "symbol" ? function(t) {
    return typeof t;
  } : function(t) {
    return t && typeof Symbol == "function" && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t;
  }, cd(e);
}
var qy = {
  click: "onClick",
  mousedown: "onMouseDown",
  mouseup: "onMouseUp",
  mouseover: "onMouseOver",
  mousemove: "onMouseMove",
  mouseout: "onMouseOut",
  mouseenter: "onMouseEnter",
  mouseleave: "onMouseLeave",
  touchcancel: "onTouchCancel",
  touchend: "onTouchEnd",
  touchmove: "onTouchMove",
  touchstart: "onTouchStart",
  contextmenu: "onContextMenu",
  dblclick: "onDoubleClick"
}, fr = function(t) {
  return typeof t == "string" ? t : t ? t.displayName || t.name || "Component" : "";
}, By = null, ls = null, Yh = function e(t) {
  if (t === By && Array.isArray(ls))
    return ls;
  var r = [];
  return $r.forEach(t, function(n) {
    me(n) || (cT.isFragment(n) ? r = r.concat(e(n.props.children)) : r.push(n));
  }), ls = r, By = t, r;
};
function qt(e, t) {
  var r = [], n = [];
  return Array.isArray(t) ? n = t.map(function(a) {
    return fr(a);
  }) : n = [fr(t)], Yh(e).forEach(function(a) {
    var i = Tt(a, "type.displayName") || Tt(a, "type.name");
    n.indexOf(i) !== -1 && r.push(a);
  }), r;
}
function vt(e, t) {
  var r = qt(e, t);
  return r && r[0];
}
var Fy = function(t) {
  if (!t || !t.props)
    return !1;
  var r = t.props, n = r.width, a = r.height;
  return !(!H(n) || n <= 0 || !H(a) || a <= 0);
}, _T = ["a", "altGlyph", "altGlyphDef", "altGlyphItem", "animate", "animateColor", "animateMotion", "animateTransform", "circle", "clipPath", "color-profile", "cursor", "defs", "desc", "ellipse", "feBlend", "feColormatrix", "feComponentTransfer", "feComposite", "feConvolveMatrix", "feDiffuseLighting", "feDisplacementMap", "feDistantLight", "feFlood", "feFuncA", "feFuncB", "feFuncG", "feFuncR", "feGaussianBlur", "feImage", "feMerge", "feMergeNode", "feMorphology", "feOffset", "fePointLight", "feSpecularLighting", "feSpotLight", "feTile", "feTurbulence", "filter", "font", "font-face", "font-face-format", "font-face-name", "font-face-url", "foreignObject", "g", "glyph", "glyphRef", "hkern", "image", "line", "lineGradient", "marker", "mask", "metadata", "missing-glyph", "mpath", "path", "pattern", "polygon", "polyline", "radialGradient", "rect", "script", "set", "stop", "style", "svg", "switch", "symbol", "text", "textPath", "title", "tref", "tspan", "use", "view", "vkern"], ST = function(t) {
  return t && t.type && Ci(t.type) && _T.indexOf(t.type) >= 0;
}, PT = function(t) {
  return t && cd(t) === "object" && "clipDot" in t;
}, AT = function(t, r, n, a) {
  var i, o = (i = us?.[a]) !== null && i !== void 0 ? i : [];
  return r.startsWith("data-") || !fe(t) && (a && o.includes(r) || gT.includes(r)) || n && Xh.includes(r);
}, pe = function(t, r, n) {
  if (!t || typeof t == "function" || typeof t == "boolean")
    return null;
  var a = t;
  if (/* @__PURE__ */ Lt(t) && (a = t.props), !aa(a))
    return null;
  var i = {};
  return Object.keys(a).forEach(function(o) {
    var u;
    AT((u = a) === null || u === void 0 ? void 0 : u[o], o, r, n) && (i[o] = a[o]);
  }), i;
}, fd = function e(t, r) {
  if (t === r)
    return !0;
  var n = $r.count(t);
  if (n !== $r.count(r))
    return !1;
  if (n === 0)
    return !0;
  if (n === 1)
    return zy(Array.isArray(t) ? t[0] : t, Array.isArray(r) ? r[0] : r);
  for (var a = 0; a < n; a++) {
    var i = t[a], o = r[a];
    if (Array.isArray(i) || Array.isArray(o)) {
      if (!e(i, o))
        return !1;
    } else if (!zy(i, o))
      return !1;
  }
  return !0;
}, zy = function(t, r) {
  if (me(t) && me(r))
    return !0;
  if (!me(t) && !me(r)) {
    var n = t.props || {}, a = n.children, i = Ly(n, xT), o = r.props || {}, u = o.children, l = Ly(o, wT);
    return a && u ? Nn(i, l) && fd(a, u) : !a && !u ? Nn(i, l) : !1;
  }
  return !1;
}, Uy = function(t, r) {
  var n = [], a = {};
  return Yh(t).forEach(function(i, o) {
    if (ST(i))
      n.push(i);
    else if (i) {
      var u = fr(i.type), l = r[u] || {}, s = l.handler, f = l.once;
      if (s && (!f || !a[u])) {
        var c = s(i, u, o);
        n.push(c), a[u] = !0;
      }
    }
  }), n;
}, ET = function(t) {
  var r = t && t.type;
  return r && qy[r] ? qy[r] : null;
}, TT = function(t, r) {
  return Yh(r).indexOf(t);
}, MT = ["children", "width", "height", "viewBox", "className", "style", "title", "desc"];
function dd() {
  return dd = Object.assign ? Object.assign.bind() : function(e) {
    for (var t = 1; t < arguments.length; t++) {
      var r = arguments[t];
      for (var n in r)
        Object.prototype.hasOwnProperty.call(r, n) && (e[n] = r[n]);
    }
    return e;
  }, dd.apply(this, arguments);
}
function jT(e, t) {
  if (e == null) return {};
  var r = NT(e, t), n, a;
  if (Object.getOwnPropertySymbols) {
    var i = Object.getOwnPropertySymbols(e);
    for (a = 0; a < i.length; a++)
      n = i[a], !(t.indexOf(n) >= 0) && Object.prototype.propertyIsEnumerable.call(e, n) && (r[n] = e[n]);
  }
  return r;
}
function NT(e, t) {
  if (e == null) return {};
  var r = {};
  for (var n in e)
    if (Object.prototype.hasOwnProperty.call(e, n)) {
      if (t.indexOf(n) >= 0) continue;
      r[n] = e[n];
    }
  return r;
}
function hd(e) {
  var t = e.children, r = e.width, n = e.height, a = e.viewBox, i = e.className, o = e.style, u = e.title, l = e.desc, s = jT(e, MT), f = a || {
    width: r,
    height: n,
    x: 0,
    y: 0
  }, c = _e("recharts-surface", i);
  return /* @__PURE__ */ M.createElement("svg", dd({}, pe(s, !0, "svg"), {
    className: c,
    width: r,
    height: n,
    style: o,
    viewBox: "".concat(f.x, " ").concat(f.y, " ").concat(f.width, " ").concat(f.height)
  }), /* @__PURE__ */ M.createElement("title", null, u), /* @__PURE__ */ M.createElement("desc", null, l), t);
}
var CT = ["children", "className"];
function pd() {
  return pd = Object.assign ? Object.assign.bind() : function(e) {
    for (var t = 1; t < arguments.length; t++) {
      var r = arguments[t];
      for (var n in r)
        Object.prototype.hasOwnProperty.call(r, n) && (e[n] = r[n]);
    }
    return e;
  }, pd.apply(this, arguments);
}
function $T(e, t) {
  if (e == null) return {};
  var r = RT(e, t), n, a;
  if (Object.getOwnPropertySymbols) {
    var i = Object.getOwnPropertySymbols(e);
    for (a = 0; a < i.length; a++)
      n = i[a], !(t.indexOf(n) >= 0) && Object.prototype.propertyIsEnumerable.call(e, n) && (r[n] = e[n]);
  }
  return r;
}
function RT(e, t) {
  if (e == null) return {};
  var r = {};
  for (var n in e)
    if (Object.prototype.hasOwnProperty.call(e, n)) {
      if (t.indexOf(n) >= 0) continue;
      r[n] = e[n];
    }
  return r;
}
var Ie = /* @__PURE__ */ M.forwardRef(function(e, t) {
  var r = e.children, n = e.className, a = $T(e, CT), i = _e("recharts-layer", n);
  return /* @__PURE__ */ M.createElement("g", pd({
    className: i
  }, pe(a, !0), {
    ref: t
  }), r);
}), dr = function(t, r) {
  for (var n = arguments.length, a = new Array(n > 2 ? n - 2 : 0), i = 2; i < n; i++)
    a[i - 2] = arguments[i];
}, ss, Wy;
function kT() {
  if (Wy) return ss;
  Wy = 1;
  function e(t, r, n) {
    var a = -1, i = t.length;
    r < 0 && (r = -r > i ? 0 : i + r), n = n > i ? i : n, n < 0 && (n += i), i = r > n ? 0 : n - r >>> 0, r >>>= 0;
    for (var o = Array(i); ++a < i; )
      o[a] = t[a + r];
    return o;
  }
  return ss = e, ss;
}
var cs, Hy;
function IT() {
  if (Hy) return cs;
  Hy = 1;
  var e = kT();
  function t(r, n, a) {
    var i = r.length;
    return a = a === void 0 ? i : a, !n && a >= i ? r : e(r, n, a);
  }
  return cs = t, cs;
}
var fs, Gy;
function vw() {
  if (Gy) return fs;
  Gy = 1;
  var e = "\\ud800-\\udfff", t = "\\u0300-\\u036f", r = "\\ufe20-\\ufe2f", n = "\\u20d0-\\u20ff", a = t + r + n, i = "\\ufe0e\\ufe0f", o = "\\u200d", u = RegExp("[" + o + e + a + i + "]");
  function l(s) {
    return u.test(s);
  }
  return fs = l, fs;
}
var ds, Ky;
function DT() {
  if (Ky) return ds;
  Ky = 1;
  function e(t) {
    return t.split("");
  }
  return ds = e, ds;
}
var hs, Vy;
function LT() {
  if (Vy) return hs;
  Vy = 1;
  var e = "\\ud800-\\udfff", t = "\\u0300-\\u036f", r = "\\ufe20-\\ufe2f", n = "\\u20d0-\\u20ff", a = t + r + n, i = "\\ufe0e\\ufe0f", o = "[" + e + "]", u = "[" + a + "]", l = "\\ud83c[\\udffb-\\udfff]", s = "(?:" + u + "|" + l + ")", f = "[^" + e + "]", c = "(?:\\ud83c[\\udde6-\\uddff]){2}", d = "[\\ud800-\\udbff][\\udc00-\\udfff]", h = "\\u200d", y = s + "?", v = "[" + i + "]?", p = "(?:" + h + "(?:" + [f, c, d].join("|") + ")" + v + y + ")*", g = v + y + p, b = "(?:" + [f + u + "?", u, c, d, o].join("|") + ")", w = RegExp(l + "(?=" + l + ")|" + b + g, "g");
  function _(m) {
    return m.match(w) || [];
  }
  return hs = _, hs;
}
var ps, Xy;
function qT() {
  if (Xy) return ps;
  Xy = 1;
  var e = DT(), t = vw(), r = LT();
  function n(a) {
    return t(a) ? r(a) : e(a);
  }
  return ps = n, ps;
}
var vs, Yy;
function BT() {
  if (Yy) return vs;
  Yy = 1;
  var e = IT(), t = vw(), r = qT(), n = fw();
  function a(i) {
    return function(o) {
      o = n(o);
      var u = t(o) ? r(o) : void 0, l = u ? u[0] : o.charAt(0), s = u ? e(u, 1).join("") : o.slice(1);
      return l[i]() + s;
    };
  }
  return vs = a, vs;
}
var ys, Zy;
function FT() {
  if (Zy) return ys;
  Zy = 1;
  var e = BT(), t = e("toUpperCase");
  return ys = t, ys;
}
var zT = FT();
const Ou = /* @__PURE__ */ $e(zT);
function Ce(e) {
  return function() {
    return e;
  };
}
const yw = Math.cos, bo = Math.sin, Ft = Math.sqrt, xo = Math.PI, _u = 2 * xo, vd = Math.PI, yd = 2 * vd, Vr = 1e-6, UT = yd - Vr;
function mw(e) {
  this._ += e[0];
  for (let t = 1, r = e.length; t < r; ++t)
    this._ += arguments[t] + e[t];
}
function WT(e) {
  let t = Math.floor(e);
  if (!(t >= 0)) throw new Error(`invalid digits: ${e}`);
  if (t > 15) return mw;
  const r = 10 ** t;
  return function(n) {
    this._ += n[0];
    for (let a = 1, i = n.length; a < i; ++a)
      this._ += Math.round(arguments[a] * r) / r + n[a];
  };
}
class HT {
  constructor(t) {
    this._x0 = this._y0 = // start of current subpath
    this._x1 = this._y1 = null, this._ = "", this._append = t == null ? mw : WT(t);
  }
  moveTo(t, r) {
    this._append`M${this._x0 = this._x1 = +t},${this._y0 = this._y1 = +r}`;
  }
  closePath() {
    this._x1 !== null && (this._x1 = this._x0, this._y1 = this._y0, this._append`Z`);
  }
  lineTo(t, r) {
    this._append`L${this._x1 = +t},${this._y1 = +r}`;
  }
  quadraticCurveTo(t, r, n, a) {
    this._append`Q${+t},${+r},${this._x1 = +n},${this._y1 = +a}`;
  }
  bezierCurveTo(t, r, n, a, i, o) {
    this._append`C${+t},${+r},${+n},${+a},${this._x1 = +i},${this._y1 = +o}`;
  }
  arcTo(t, r, n, a, i) {
    if (t = +t, r = +r, n = +n, a = +a, i = +i, i < 0) throw new Error(`negative radius: ${i}`);
    let o = this._x1, u = this._y1, l = n - t, s = a - r, f = o - t, c = u - r, d = f * f + c * c;
    if (this._x1 === null)
      this._append`M${this._x1 = t},${this._y1 = r}`;
    else if (d > Vr) if (!(Math.abs(c * l - s * f) > Vr) || !i)
      this._append`L${this._x1 = t},${this._y1 = r}`;
    else {
      let h = n - o, y = a - u, v = l * l + s * s, p = h * h + y * y, g = Math.sqrt(v), b = Math.sqrt(d), w = i * Math.tan((vd - Math.acos((v + d - p) / (2 * g * b))) / 2), _ = w / b, m = w / g;
      Math.abs(_ - 1) > Vr && this._append`L${t + _ * f},${r + _ * c}`, this._append`A${i},${i},0,0,${+(c * h > f * y)},${this._x1 = t + m * l},${this._y1 = r + m * s}`;
    }
  }
  arc(t, r, n, a, i, o) {
    if (t = +t, r = +r, n = +n, o = !!o, n < 0) throw new Error(`negative radius: ${n}`);
    let u = n * Math.cos(a), l = n * Math.sin(a), s = t + u, f = r + l, c = 1 ^ o, d = o ? a - i : i - a;
    this._x1 === null ? this._append`M${s},${f}` : (Math.abs(this._x1 - s) > Vr || Math.abs(this._y1 - f) > Vr) && this._append`L${s},${f}`, n && (d < 0 && (d = d % yd + yd), d > UT ? this._append`A${n},${n},0,1,${c},${t - u},${r - l}A${n},${n},0,1,${c},${this._x1 = s},${this._y1 = f}` : d > Vr && this._append`A${n},${n},0,${+(d >= vd)},${c},${this._x1 = t + n * Math.cos(i)},${this._y1 = r + n * Math.sin(i)}`);
  }
  rect(t, r, n, a) {
    this._append`M${this._x0 = this._x1 = +t},${this._y0 = this._y1 = +r}h${n = +n}v${+a}h${-n}Z`;
  }
  toString() {
    return this._;
  }
}
function Zh(e) {
  let t = 3;
  return e.digits = function(r) {
    if (!arguments.length) return t;
    if (r == null)
      t = null;
    else {
      const n = Math.floor(r);
      if (!(n >= 0)) throw new RangeError(`invalid digits: ${r}`);
      t = n;
    }
    return e;
  }, () => new HT(t);
}
function Jh(e) {
  return typeof e == "object" && "length" in e ? e : Array.from(e);
}
function gw(e) {
  this._context = e;
}
gw.prototype = {
  areaStart: function() {
    this._line = 0;
  },
  areaEnd: function() {
    this._line = NaN;
  },
  lineStart: function() {
    this._point = 0;
  },
  lineEnd: function() {
    (this._line || this._line !== 0 && this._point === 1) && this._context.closePath(), this._line = 1 - this._line;
  },
  point: function(e, t) {
    switch (e = +e, t = +t, this._point) {
      case 0:
        this._point = 1, this._line ? this._context.lineTo(e, t) : this._context.moveTo(e, t);
        break;
      case 1:
        this._point = 2;
      // falls through
      default:
        this._context.lineTo(e, t);
        break;
    }
  }
};
function Su(e) {
  return new gw(e);
}
function bw(e) {
  return e[0];
}
function xw(e) {
  return e[1];
}
function ww(e, t) {
  var r = Ce(!0), n = null, a = Su, i = null, o = Zh(u);
  e = typeof e == "function" ? e : e === void 0 ? bw : Ce(e), t = typeof t == "function" ? t : t === void 0 ? xw : Ce(t);
  function u(l) {
    var s, f = (l = Jh(l)).length, c, d = !1, h;
    for (n == null && (i = a(h = o())), s = 0; s <= f; ++s)
      !(s < f && r(c = l[s], s, l)) === d && ((d = !d) ? i.lineStart() : i.lineEnd()), d && i.point(+e(c, s, l), +t(c, s, l));
    if (h) return i = null, h + "" || null;
  }
  return u.x = function(l) {
    return arguments.length ? (e = typeof l == "function" ? l : Ce(+l), u) : e;
  }, u.y = function(l) {
    return arguments.length ? (t = typeof l == "function" ? l : Ce(+l), u) : t;
  }, u.defined = function(l) {
    return arguments.length ? (r = typeof l == "function" ? l : Ce(!!l), u) : r;
  }, u.curve = function(l) {
    return arguments.length ? (a = l, n != null && (i = a(n)), u) : a;
  }, u.context = function(l) {
    return arguments.length ? (l == null ? n = i = null : i = a(n = l), u) : n;
  }, u;
}
function Yi(e, t, r) {
  var n = null, a = Ce(!0), i = null, o = Su, u = null, l = Zh(s);
  e = typeof e == "function" ? e : e === void 0 ? bw : Ce(+e), t = typeof t == "function" ? t : Ce(t === void 0 ? 0 : +t), r = typeof r == "function" ? r : r === void 0 ? xw : Ce(+r);
  function s(c) {
    var d, h, y, v = (c = Jh(c)).length, p, g = !1, b, w = new Array(v), _ = new Array(v);
    for (i == null && (u = o(b = l())), d = 0; d <= v; ++d) {
      if (!(d < v && a(p = c[d], d, c)) === g)
        if (g = !g)
          h = d, u.areaStart(), u.lineStart();
        else {
          for (u.lineEnd(), u.lineStart(), y = d - 1; y >= h; --y)
            u.point(w[y], _[y]);
          u.lineEnd(), u.areaEnd();
        }
      g && (w[d] = +e(p, d, c), _[d] = +t(p, d, c), u.point(n ? +n(p, d, c) : w[d], r ? +r(p, d, c) : _[d]));
    }
    if (b) return u = null, b + "" || null;
  }
  function f() {
    return ww().defined(a).curve(o).context(i);
  }
  return s.x = function(c) {
    return arguments.length ? (e = typeof c == "function" ? c : Ce(+c), n = null, s) : e;
  }, s.x0 = function(c) {
    return arguments.length ? (e = typeof c == "function" ? c : Ce(+c), s) : e;
  }, s.x1 = function(c) {
    return arguments.length ? (n = c == null ? null : typeof c == "function" ? c : Ce(+c), s) : n;
  }, s.y = function(c) {
    return arguments.length ? (t = typeof c == "function" ? c : Ce(+c), r = null, s) : t;
  }, s.y0 = function(c) {
    return arguments.length ? (t = typeof c == "function" ? c : Ce(+c), s) : t;
  }, s.y1 = function(c) {
    return arguments.length ? (r = c == null ? null : typeof c == "function" ? c : Ce(+c), s) : r;
  }, s.lineX0 = s.lineY0 = function() {
    return f().x(e).y(t);
  }, s.lineY1 = function() {
    return f().x(e).y(r);
  }, s.lineX1 = function() {
    return f().x(n).y(t);
  }, s.defined = function(c) {
    return arguments.length ? (a = typeof c == "function" ? c : Ce(!!c), s) : a;
  }, s.curve = function(c) {
    return arguments.length ? (o = c, i != null && (u = o(i)), s) : o;
  }, s.context = function(c) {
    return arguments.length ? (c == null ? i = u = null : u = o(i = c), s) : i;
  }, s;
}
class Ow {
  constructor(t, r) {
    this._context = t, this._x = r;
  }
  areaStart() {
    this._line = 0;
  }
  areaEnd() {
    this._line = NaN;
  }
  lineStart() {
    this._point = 0;
  }
  lineEnd() {
    (this._line || this._line !== 0 && this._point === 1) && this._context.closePath(), this._line = 1 - this._line;
  }
  point(t, r) {
    switch (t = +t, r = +r, this._point) {
      case 0: {
        this._point = 1, this._line ? this._context.lineTo(t, r) : this._context.moveTo(t, r);
        break;
      }
      case 1:
        this._point = 2;
      // falls through
      default: {
        this._x ? this._context.bezierCurveTo(this._x0 = (this._x0 + t) / 2, this._y0, this._x0, r, t, r) : this._context.bezierCurveTo(this._x0, this._y0 = (this._y0 + r) / 2, t, this._y0, t, r);
        break;
      }
    }
    this._x0 = t, this._y0 = r;
  }
}
function GT(e) {
  return new Ow(e, !0);
}
function KT(e) {
  return new Ow(e, !1);
}
const Qh = {
  draw(e, t) {
    const r = Ft(t / xo);
    e.moveTo(r, 0), e.arc(0, 0, r, 0, _u);
  }
}, VT = {
  draw(e, t) {
    const r = Ft(t / 5) / 2;
    e.moveTo(-3 * r, -r), e.lineTo(-r, -r), e.lineTo(-r, -3 * r), e.lineTo(r, -3 * r), e.lineTo(r, -r), e.lineTo(3 * r, -r), e.lineTo(3 * r, r), e.lineTo(r, r), e.lineTo(r, 3 * r), e.lineTo(-r, 3 * r), e.lineTo(-r, r), e.lineTo(-3 * r, r), e.closePath();
  }
}, _w = Ft(1 / 3), XT = _w * 2, YT = {
  draw(e, t) {
    const r = Ft(t / XT), n = r * _w;
    e.moveTo(0, -r), e.lineTo(n, 0), e.lineTo(0, r), e.lineTo(-n, 0), e.closePath();
  }
}, ZT = {
  draw(e, t) {
    const r = Ft(t), n = -r / 2;
    e.rect(n, n, r, r);
  }
}, JT = 0.8908130915292852, Sw = bo(xo / 10) / bo(7 * xo / 10), QT = bo(_u / 10) * Sw, eM = -yw(_u / 10) * Sw, tM = {
  draw(e, t) {
    const r = Ft(t * JT), n = QT * r, a = eM * r;
    e.moveTo(0, -r), e.lineTo(n, a);
    for (let i = 1; i < 5; ++i) {
      const o = _u * i / 5, u = yw(o), l = bo(o);
      e.lineTo(l * r, -u * r), e.lineTo(u * n - l * a, l * n + u * a);
    }
    e.closePath();
  }
}, ms = Ft(3), rM = {
  draw(e, t) {
    const r = -Ft(t / (ms * 3));
    e.moveTo(0, r * 2), e.lineTo(-ms * r, -r), e.lineTo(ms * r, -r), e.closePath();
  }
}, wt = -0.5, Ot = Ft(3) / 2, md = 1 / Ft(12), nM = (md / 2 + 1) * 3, aM = {
  draw(e, t) {
    const r = Ft(t / nM), n = r / 2, a = r * md, i = n, o = r * md + r, u = -i, l = o;
    e.moveTo(n, a), e.lineTo(i, o), e.lineTo(u, l), e.lineTo(wt * n - Ot * a, Ot * n + wt * a), e.lineTo(wt * i - Ot * o, Ot * i + wt * o), e.lineTo(wt * u - Ot * l, Ot * u + wt * l), e.lineTo(wt * n + Ot * a, wt * a - Ot * n), e.lineTo(wt * i + Ot * o, wt * o - Ot * i), e.lineTo(wt * u + Ot * l, wt * l - Ot * u), e.closePath();
  }
};
function iM(e, t) {
  let r = null, n = Zh(a);
  e = typeof e == "function" ? e : Ce(e || Qh), t = typeof t == "function" ? t : Ce(t === void 0 ? 64 : +t);
  function a() {
    let i;
    if (r || (r = i = n()), e.apply(this, arguments).draw(r, +t.apply(this, arguments)), i) return r = null, i + "" || null;
  }
  return a.type = function(i) {
    return arguments.length ? (e = typeof i == "function" ? i : Ce(i), a) : e;
  }, a.size = function(i) {
    return arguments.length ? (t = typeof i == "function" ? i : Ce(+i), a) : t;
  }, a.context = function(i) {
    return arguments.length ? (r = i ?? null, a) : r;
  }, a;
}
function wo() {
}
function Oo(e, t, r) {
  e._context.bezierCurveTo(
    (2 * e._x0 + e._x1) / 3,
    (2 * e._y0 + e._y1) / 3,
    (e._x0 + 2 * e._x1) / 3,
    (e._y0 + 2 * e._y1) / 3,
    (e._x0 + 4 * e._x1 + t) / 6,
    (e._y0 + 4 * e._y1 + r) / 6
  );
}
function Pw(e) {
  this._context = e;
}
Pw.prototype = {
  areaStart: function() {
    this._line = 0;
  },
  areaEnd: function() {
    this._line = NaN;
  },
  lineStart: function() {
    this._x0 = this._x1 = this._y0 = this._y1 = NaN, this._point = 0;
  },
  lineEnd: function() {
    switch (this._point) {
      case 3:
        Oo(this, this._x1, this._y1);
      // falls through
      case 2:
        this._context.lineTo(this._x1, this._y1);
        break;
    }
    (this._line || this._line !== 0 && this._point === 1) && this._context.closePath(), this._line = 1 - this._line;
  },
  point: function(e, t) {
    switch (e = +e, t = +t, this._point) {
      case 0:
        this._point = 1, this._line ? this._context.lineTo(e, t) : this._context.moveTo(e, t);
        break;
      case 1:
        this._point = 2;
        break;
      case 2:
        this._point = 3, this._context.lineTo((5 * this._x0 + this._x1) / 6, (5 * this._y0 + this._y1) / 6);
      // falls through
      default:
        Oo(this, e, t);
        break;
    }
    this._x0 = this._x1, this._x1 = e, this._y0 = this._y1, this._y1 = t;
  }
};
function oM(e) {
  return new Pw(e);
}
function Aw(e) {
  this._context = e;
}
Aw.prototype = {
  areaStart: wo,
  areaEnd: wo,
  lineStart: function() {
    this._x0 = this._x1 = this._x2 = this._x3 = this._x4 = this._y0 = this._y1 = this._y2 = this._y3 = this._y4 = NaN, this._point = 0;
  },
  lineEnd: function() {
    switch (this._point) {
      case 1: {
        this._context.moveTo(this._x2, this._y2), this._context.closePath();
        break;
      }
      case 2: {
        this._context.moveTo((this._x2 + 2 * this._x3) / 3, (this._y2 + 2 * this._y3) / 3), this._context.lineTo((this._x3 + 2 * this._x2) / 3, (this._y3 + 2 * this._y2) / 3), this._context.closePath();
        break;
      }
      case 3: {
        this.point(this._x2, this._y2), this.point(this._x3, this._y3), this.point(this._x4, this._y4);
        break;
      }
    }
  },
  point: function(e, t) {
    switch (e = +e, t = +t, this._point) {
      case 0:
        this._point = 1, this._x2 = e, this._y2 = t;
        break;
      case 1:
        this._point = 2, this._x3 = e, this._y3 = t;
        break;
      case 2:
        this._point = 3, this._x4 = e, this._y4 = t, this._context.moveTo((this._x0 + 4 * this._x1 + e) / 6, (this._y0 + 4 * this._y1 + t) / 6);
        break;
      default:
        Oo(this, e, t);
        break;
    }
    this._x0 = this._x1, this._x1 = e, this._y0 = this._y1, this._y1 = t;
  }
};
function uM(e) {
  return new Aw(e);
}
function Ew(e) {
  this._context = e;
}
Ew.prototype = {
  areaStart: function() {
    this._line = 0;
  },
  areaEnd: function() {
    this._line = NaN;
  },
  lineStart: function() {
    this._x0 = this._x1 = this._y0 = this._y1 = NaN, this._point = 0;
  },
  lineEnd: function() {
    (this._line || this._line !== 0 && this._point === 3) && this._context.closePath(), this._line = 1 - this._line;
  },
  point: function(e, t) {
    switch (e = +e, t = +t, this._point) {
      case 0:
        this._point = 1;
        break;
      case 1:
        this._point = 2;
        break;
      case 2:
        this._point = 3;
        var r = (this._x0 + 4 * this._x1 + e) / 6, n = (this._y0 + 4 * this._y1 + t) / 6;
        this._line ? this._context.lineTo(r, n) : this._context.moveTo(r, n);
        break;
      case 3:
        this._point = 4;
      // falls through
      default:
        Oo(this, e, t);
        break;
    }
    this._x0 = this._x1, this._x1 = e, this._y0 = this._y1, this._y1 = t;
  }
};
function lM(e) {
  return new Ew(e);
}
function Tw(e) {
  this._context = e;
}
Tw.prototype = {
  areaStart: wo,
  areaEnd: wo,
  lineStart: function() {
    this._point = 0;
  },
  lineEnd: function() {
    this._point && this._context.closePath();
  },
  point: function(e, t) {
    e = +e, t = +t, this._point ? this._context.lineTo(e, t) : (this._point = 1, this._context.moveTo(e, t));
  }
};
function sM(e) {
  return new Tw(e);
}
function Jy(e) {
  return e < 0 ? -1 : 1;
}
function Qy(e, t, r) {
  var n = e._x1 - e._x0, a = t - e._x1, i = (e._y1 - e._y0) / (n || a < 0 && -0), o = (r - e._y1) / (a || n < 0 && -0), u = (i * a + o * n) / (n + a);
  return (Jy(i) + Jy(o)) * Math.min(Math.abs(i), Math.abs(o), 0.5 * Math.abs(u)) || 0;
}
function em(e, t) {
  var r = e._x1 - e._x0;
  return r ? (3 * (e._y1 - e._y0) / r - t) / 2 : t;
}
function gs(e, t, r) {
  var n = e._x0, a = e._y0, i = e._x1, o = e._y1, u = (i - n) / 3;
  e._context.bezierCurveTo(n + u, a + u * t, i - u, o - u * r, i, o);
}
function _o(e) {
  this._context = e;
}
_o.prototype = {
  areaStart: function() {
    this._line = 0;
  },
  areaEnd: function() {
    this._line = NaN;
  },
  lineStart: function() {
    this._x0 = this._x1 = this._y0 = this._y1 = this._t0 = NaN, this._point = 0;
  },
  lineEnd: function() {
    switch (this._point) {
      case 2:
        this._context.lineTo(this._x1, this._y1);
        break;
      case 3:
        gs(this, this._t0, em(this, this._t0));
        break;
    }
    (this._line || this._line !== 0 && this._point === 1) && this._context.closePath(), this._line = 1 - this._line;
  },
  point: function(e, t) {
    var r = NaN;
    if (e = +e, t = +t, !(e === this._x1 && t === this._y1)) {
      switch (this._point) {
        case 0:
          this._point = 1, this._line ? this._context.lineTo(e, t) : this._context.moveTo(e, t);
          break;
        case 1:
          this._point = 2;
          break;
        case 2:
          this._point = 3, gs(this, em(this, r = Qy(this, e, t)), r);
          break;
        default:
          gs(this, this._t0, r = Qy(this, e, t));
          break;
      }
      this._x0 = this._x1, this._x1 = e, this._y0 = this._y1, this._y1 = t, this._t0 = r;
    }
  }
};
function Mw(e) {
  this._context = new jw(e);
}
(Mw.prototype = Object.create(_o.prototype)).point = function(e, t) {
  _o.prototype.point.call(this, t, e);
};
function jw(e) {
  this._context = e;
}
jw.prototype = {
  moveTo: function(e, t) {
    this._context.moveTo(t, e);
  },
  closePath: function() {
    this._context.closePath();
  },
  lineTo: function(e, t) {
    this._context.lineTo(t, e);
  },
  bezierCurveTo: function(e, t, r, n, a, i) {
    this._context.bezierCurveTo(t, e, n, r, i, a);
  }
};
function cM(e) {
  return new _o(e);
}
function fM(e) {
  return new Mw(e);
}
function Nw(e) {
  this._context = e;
}
Nw.prototype = {
  areaStart: function() {
    this._line = 0;
  },
  areaEnd: function() {
    this._line = NaN;
  },
  lineStart: function() {
    this._x = [], this._y = [];
  },
  lineEnd: function() {
    var e = this._x, t = this._y, r = e.length;
    if (r)
      if (this._line ? this._context.lineTo(e[0], t[0]) : this._context.moveTo(e[0], t[0]), r === 2)
        this._context.lineTo(e[1], t[1]);
      else
        for (var n = tm(e), a = tm(t), i = 0, o = 1; o < r; ++i, ++o)
          this._context.bezierCurveTo(n[0][i], a[0][i], n[1][i], a[1][i], e[o], t[o]);
    (this._line || this._line !== 0 && r === 1) && this._context.closePath(), this._line = 1 - this._line, this._x = this._y = null;
  },
  point: function(e, t) {
    this._x.push(+e), this._y.push(+t);
  }
};
function tm(e) {
  var t, r = e.length - 1, n, a = new Array(r), i = new Array(r), o = new Array(r);
  for (a[0] = 0, i[0] = 2, o[0] = e[0] + 2 * e[1], t = 1; t < r - 1; ++t) a[t] = 1, i[t] = 4, o[t] = 4 * e[t] + 2 * e[t + 1];
  for (a[r - 1] = 2, i[r - 1] = 7, o[r - 1] = 8 * e[r - 1] + e[r], t = 1; t < r; ++t) n = a[t] / i[t - 1], i[t] -= n, o[t] -= n * o[t - 1];
  for (a[r - 1] = o[r - 1] / i[r - 1], t = r - 2; t >= 0; --t) a[t] = (o[t] - a[t + 1]) / i[t];
  for (i[r - 1] = (e[r] + a[r - 1]) / 2, t = 0; t < r - 1; ++t) i[t] = 2 * e[t + 1] - a[t + 1];
  return [a, i];
}
function dM(e) {
  return new Nw(e);
}
function Pu(e, t) {
  this._context = e, this._t = t;
}
Pu.prototype = {
  areaStart: function() {
    this._line = 0;
  },
  areaEnd: function() {
    this._line = NaN;
  },
  lineStart: function() {
    this._x = this._y = NaN, this._point = 0;
  },
  lineEnd: function() {
    0 < this._t && this._t < 1 && this._point === 2 && this._context.lineTo(this._x, this._y), (this._line || this._line !== 0 && this._point === 1) && this._context.closePath(), this._line >= 0 && (this._t = 1 - this._t, this._line = 1 - this._line);
  },
  point: function(e, t) {
    switch (e = +e, t = +t, this._point) {
      case 0:
        this._point = 1, this._line ? this._context.lineTo(e, t) : this._context.moveTo(e, t);
        break;
      case 1:
        this._point = 2;
      // falls through
      default: {
        if (this._t <= 0)
          this._context.lineTo(this._x, t), this._context.lineTo(e, t);
        else {
          var r = this._x * (1 - this._t) + e * this._t;
          this._context.lineTo(r, this._y), this._context.lineTo(r, t);
        }
        break;
      }
    }
    this._x = e, this._y = t;
  }
};
function hM(e) {
  return new Pu(e, 0.5);
}
function pM(e) {
  return new Pu(e, 0);
}
function vM(e) {
  return new Pu(e, 1);
}
function kn(e, t) {
  if ((o = e.length) > 1)
    for (var r = 1, n, a, i = e[t[0]], o, u = i.length; r < o; ++r)
      for (a = i, i = e[t[r]], n = 0; n < u; ++n)
        i[n][1] += i[n][0] = isNaN(a[n][1]) ? a[n][0] : a[n][1];
}
function gd(e) {
  for (var t = e.length, r = new Array(t); --t >= 0; ) r[t] = t;
  return r;
}
function yM(e, t) {
  return e[t];
}
function mM(e) {
  const t = [];
  return t.key = e, t;
}
function gM() {
  var e = Ce([]), t = gd, r = kn, n = yM;
  function a(i) {
    var o = Array.from(e.apply(this, arguments), mM), u, l = o.length, s = -1, f;
    for (const c of i)
      for (u = 0, ++s; u < l; ++u)
        (o[u][s] = [0, +n(c, o[u].key, s, i)]).data = c;
    for (u = 0, f = Jh(t(o)); u < l; ++u)
      o[f[u]].index = u;
    return r(o, f), o;
  }
  return a.keys = function(i) {
    return arguments.length ? (e = typeof i == "function" ? i : Ce(Array.from(i)), a) : e;
  }, a.value = function(i) {
    return arguments.length ? (n = typeof i == "function" ? i : Ce(+i), a) : n;
  }, a.order = function(i) {
    return arguments.length ? (t = i == null ? gd : typeof i == "function" ? i : Ce(Array.from(i)), a) : t;
  }, a.offset = function(i) {
    return arguments.length ? (r = i ?? kn, a) : r;
  }, a;
}
function bM(e, t) {
  if ((n = e.length) > 0) {
    for (var r, n, a = 0, i = e[0].length, o; a < i; ++a) {
      for (o = r = 0; r < n; ++r) o += e[r][a][1] || 0;
      if (o) for (r = 0; r < n; ++r) e[r][a][1] /= o;
    }
    kn(e, t);
  }
}
function xM(e, t) {
  if ((a = e.length) > 0) {
    for (var r = 0, n = e[t[0]], a, i = n.length; r < i; ++r) {
      for (var o = 0, u = 0; o < a; ++o) u += e[o][r][1] || 0;
      n[r][1] += n[r][0] = -u / 2;
    }
    kn(e, t);
  }
}
function wM(e, t) {
  if (!(!((o = e.length) > 0) || !((i = (a = e[t[0]]).length) > 0))) {
    for (var r = 0, n = 1, a, i, o; n < i; ++n) {
      for (var u = 0, l = 0, s = 0; u < o; ++u) {
        for (var f = e[t[u]], c = f[n][1] || 0, d = f[n - 1][1] || 0, h = (c - d) / 2, y = 0; y < u; ++y) {
          var v = e[t[y]], p = v[n][1] || 0, g = v[n - 1][1] || 0;
          h += p - g;
        }
        l += c, s += h * c;
      }
      a[n - 1][1] += a[n - 1][0] = r, l && (r -= s / l);
    }
    a[n - 1][1] += a[n - 1][0] = r, kn(e, t);
  }
}
function Wa(e) {
  "@babel/helpers - typeof";
  return Wa = typeof Symbol == "function" && typeof Symbol.iterator == "symbol" ? function(t) {
    return typeof t;
  } : function(t) {
    return t && typeof Symbol == "function" && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t;
  }, Wa(e);
}
var OM = ["type", "size", "sizeType"];
function bd() {
  return bd = Object.assign ? Object.assign.bind() : function(e) {
    for (var t = 1; t < arguments.length; t++) {
      var r = arguments[t];
      for (var n in r)
        Object.prototype.hasOwnProperty.call(r, n) && (e[n] = r[n]);
    }
    return e;
  }, bd.apply(this, arguments);
}
function rm(e, t) {
  var r = Object.keys(e);
  if (Object.getOwnPropertySymbols) {
    var n = Object.getOwnPropertySymbols(e);
    t && (n = n.filter(function(a) {
      return Object.getOwnPropertyDescriptor(e, a).enumerable;
    })), r.push.apply(r, n);
  }
  return r;
}
function nm(e) {
  for (var t = 1; t < arguments.length; t++) {
    var r = arguments[t] != null ? arguments[t] : {};
    t % 2 ? rm(Object(r), !0).forEach(function(n) {
      _M(e, n, r[n]);
    }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(r)) : rm(Object(r)).forEach(function(n) {
      Object.defineProperty(e, n, Object.getOwnPropertyDescriptor(r, n));
    });
  }
  return e;
}
function _M(e, t, r) {
  return t = SM(t), t in e ? Object.defineProperty(e, t, { value: r, enumerable: !0, configurable: !0, writable: !0 }) : e[t] = r, e;
}
function SM(e) {
  var t = PM(e, "string");
  return Wa(t) == "symbol" ? t : t + "";
}
function PM(e, t) {
  if (Wa(e) != "object" || !e) return e;
  var r = e[Symbol.toPrimitive];
  if (r !== void 0) {
    var n = r.call(e, t);
    if (Wa(n) != "object") return n;
    throw new TypeError("@@toPrimitive must return a primitive value.");
  }
  return (t === "string" ? String : Number)(e);
}
function AM(e, t) {
  if (e == null) return {};
  var r = EM(e, t), n, a;
  if (Object.getOwnPropertySymbols) {
    var i = Object.getOwnPropertySymbols(e);
    for (a = 0; a < i.length; a++)
      n = i[a], !(t.indexOf(n) >= 0) && Object.prototype.propertyIsEnumerable.call(e, n) && (r[n] = e[n]);
  }
  return r;
}
function EM(e, t) {
  if (e == null) return {};
  var r = {};
  for (var n in e)
    if (Object.prototype.hasOwnProperty.call(e, n)) {
      if (t.indexOf(n) >= 0) continue;
      r[n] = e[n];
    }
  return r;
}
var Cw = {
  symbolCircle: Qh,
  symbolCross: VT,
  symbolDiamond: YT,
  symbolSquare: ZT,
  symbolStar: tM,
  symbolTriangle: rM,
  symbolWye: aM
}, TM = Math.PI / 180, MM = function(t) {
  var r = "symbol".concat(Ou(t));
  return Cw[r] || Qh;
}, jM = function(t, r, n) {
  if (r === "area")
    return t;
  switch (n) {
    case "cross":
      return 5 * t * t / 9;
    case "diamond":
      return 0.5 * t * t / Math.sqrt(3);
    case "square":
      return t * t;
    case "star": {
      var a = 18 * TM;
      return 1.25 * t * t * (Math.tan(a) - Math.tan(a * 2) * Math.pow(Math.tan(a), 2));
    }
    case "triangle":
      return Math.sqrt(3) * t * t / 4;
    case "wye":
      return (21 - 10 * Math.sqrt(3)) * t * t / 8;
    default:
      return Math.PI * t * t / 4;
  }
}, NM = function(t, r) {
  Cw["symbol".concat(Ou(t))] = r;
}, ep = function(t) {
  var r = t.type, n = r === void 0 ? "circle" : r, a = t.size, i = a === void 0 ? 64 : a, o = t.sizeType, u = o === void 0 ? "area" : o, l = AM(t, OM), s = nm(nm({}, l), {}, {
    type: n,
    size: i,
    sizeType: u
  }), f = function() {
    var p = MM(n), g = iM().type(p).size(jM(i, u, n));
    return g();
  }, c = s.className, d = s.cx, h = s.cy, y = pe(s, !0);
  return d === +d && h === +h && i === +i ? /* @__PURE__ */ M.createElement("path", bd({}, y, {
    className: _e("recharts-symbols", c),
    transform: "translate(".concat(d, ", ").concat(h, ")"),
    d: f()
  })) : null;
};
ep.registerSymbol = NM;
function In(e) {
  "@babel/helpers - typeof";
  return In = typeof Symbol == "function" && typeof Symbol.iterator == "symbol" ? function(t) {
    return typeof t;
  } : function(t) {
    return t && typeof Symbol == "function" && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t;
  }, In(e);
}
function xd() {
  return xd = Object.assign ? Object.assign.bind() : function(e) {
    for (var t = 1; t < arguments.length; t++) {
      var r = arguments[t];
      for (var n in r)
        Object.prototype.hasOwnProperty.call(r, n) && (e[n] = r[n]);
    }
    return e;
  }, xd.apply(this, arguments);
}
function am(e, t) {
  var r = Object.keys(e);
  if (Object.getOwnPropertySymbols) {
    var n = Object.getOwnPropertySymbols(e);
    t && (n = n.filter(function(a) {
      return Object.getOwnPropertyDescriptor(e, a).enumerable;
    })), r.push.apply(r, n);
  }
  return r;
}
function CM(e) {
  for (var t = 1; t < arguments.length; t++) {
    var r = arguments[t] != null ? arguments[t] : {};
    t % 2 ? am(Object(r), !0).forEach(function(n) {
      Ha(e, n, r[n]);
    }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(r)) : am(Object(r)).forEach(function(n) {
      Object.defineProperty(e, n, Object.getOwnPropertyDescriptor(r, n));
    });
  }
  return e;
}
function $M(e, t) {
  if (!(e instanceof t))
    throw new TypeError("Cannot call a class as a function");
}
function RM(e, t) {
  for (var r = 0; r < t.length; r++) {
    var n = t[r];
    n.enumerable = n.enumerable || !1, n.configurable = !0, "value" in n && (n.writable = !0), Object.defineProperty(e, Rw(n.key), n);
  }
}
function kM(e, t, r) {
  return t && RM(e.prototype, t), Object.defineProperty(e, "prototype", { writable: !1 }), e;
}
function IM(e, t, r) {
  return t = So(t), DM(e, $w() ? Reflect.construct(t, r || [], So(e).constructor) : t.apply(e, r));
}
function DM(e, t) {
  if (t && (In(t) === "object" || typeof t == "function"))
    return t;
  if (t !== void 0)
    throw new TypeError("Derived constructors may only return object or undefined");
  return LM(e);
}
function LM(e) {
  if (e === void 0)
    throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
  return e;
}
function $w() {
  try {
    var e = !Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function() {
    }));
  } catch {
  }
  return ($w = function() {
    return !!e;
  })();
}
function So(e) {
  return So = Object.setPrototypeOf ? Object.getPrototypeOf.bind() : function(r) {
    return r.__proto__ || Object.getPrototypeOf(r);
  }, So(e);
}
function qM(e, t) {
  if (typeof t != "function" && t !== null)
    throw new TypeError("Super expression must either be null or a function");
  e.prototype = Object.create(t && t.prototype, { constructor: { value: e, writable: !0, configurable: !0 } }), Object.defineProperty(e, "prototype", { writable: !1 }), t && wd(e, t);
}
function wd(e, t) {
  return wd = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function(n, a) {
    return n.__proto__ = a, n;
  }, wd(e, t);
}
function Ha(e, t, r) {
  return t = Rw(t), t in e ? Object.defineProperty(e, t, { value: r, enumerable: !0, configurable: !0, writable: !0 }) : e[t] = r, e;
}
function Rw(e) {
  var t = BM(e, "string");
  return In(t) == "symbol" ? t : t + "";
}
function BM(e, t) {
  if (In(e) != "object" || !e) return e;
  var r = e[Symbol.toPrimitive];
  if (r !== void 0) {
    var n = r.call(e, t);
    if (In(n) != "object") return n;
    throw new TypeError("@@toPrimitive must return a primitive value.");
  }
  return String(e);
}
var _t = 32, tp = /* @__PURE__ */ (function(e) {
  function t() {
    return $M(this, t), IM(this, t, arguments);
  }
  return qM(t, e), kM(t, [{
    key: "renderIcon",
    value: (
      /**
       * Render the path of icon
       * @param {Object} data Data of each legend item
       * @return {String} Path element
       */
      function(n) {
        var a = this.props.inactiveColor, i = _t / 2, o = _t / 6, u = _t / 3, l = n.inactive ? a : n.color;
        if (n.type === "plainline")
          return /* @__PURE__ */ M.createElement("line", {
            strokeWidth: 4,
            fill: "none",
            stroke: l,
            strokeDasharray: n.payload.strokeDasharray,
            x1: 0,
            y1: i,
            x2: _t,
            y2: i,
            className: "recharts-legend-icon"
          });
        if (n.type === "line")
          return /* @__PURE__ */ M.createElement("path", {
            strokeWidth: 4,
            fill: "none",
            stroke: l,
            d: "M0,".concat(i, "h").concat(u, `
            A`).concat(o, ",").concat(o, ",0,1,1,").concat(2 * u, ",").concat(i, `
            H`).concat(_t, "M").concat(2 * u, ",").concat(i, `
            A`).concat(o, ",").concat(o, ",0,1,1,").concat(u, ",").concat(i),
            className: "recharts-legend-icon"
          });
        if (n.type === "rect")
          return /* @__PURE__ */ M.createElement("path", {
            stroke: "none",
            fill: l,
            d: "M0,".concat(_t / 8, "h").concat(_t, "v").concat(_t * 3 / 4, "h").concat(-_t, "z"),
            className: "recharts-legend-icon"
          });
        if (/* @__PURE__ */ M.isValidElement(n.legendIcon)) {
          var s = CM({}, n);
          return delete s.legendIcon, /* @__PURE__ */ M.cloneElement(n.legendIcon, s);
        }
        return /* @__PURE__ */ M.createElement(ep, {
          fill: l,
          cx: i,
          cy: i,
          size: _t,
          sizeType: "diameter",
          type: n.type
        });
      }
    )
    /**
     * Draw items of legend
     * @return {ReactElement} Items
     */
  }, {
    key: "renderItems",
    value: function() {
      var n = this, a = this.props, i = a.payload, o = a.iconSize, u = a.layout, l = a.formatter, s = a.inactiveColor, f = {
        x: 0,
        y: 0,
        width: _t,
        height: _t
      }, c = {
        display: u === "horizontal" ? "inline-block" : "block",
        marginRight: 10
      }, d = {
        display: "inline-block",
        verticalAlign: "middle",
        marginRight: 4
      };
      return i.map(function(h, y) {
        var v = h.formatter || l, p = _e(Ha(Ha({
          "recharts-legend-item": !0
        }, "legend-item-".concat(y), !0), "inactive", h.inactive));
        if (h.type === "none")
          return null;
        var g = fe(h.value) ? null : h.value;
        dr(
          !fe(h.value),
          `The name property is also required when using a function for the dataKey of a chart's cartesian components. Ex: <Bar name="Name of my Data"/>`
          // eslint-disable-line max-len
        );
        var b = h.inactive ? s : h.color;
        return /* @__PURE__ */ M.createElement("li", xd({
          className: p,
          style: c,
          key: "legend-item-".concat(y)
        }, go(n.props, h, y)), /* @__PURE__ */ M.createElement(hd, {
          width: o,
          height: o,
          viewBox: f,
          style: d
        }, n.renderIcon(h)), /* @__PURE__ */ M.createElement("span", {
          className: "recharts-legend-item-text",
          style: {
            color: b
          }
        }, v ? v(g, h, y) : g));
      });
    }
  }, {
    key: "render",
    value: function() {
      var n = this.props, a = n.payload, i = n.layout, o = n.align;
      if (!a || !a.length)
        return null;
      var u = {
        padding: 0,
        margin: 0,
        textAlign: i === "horizontal" ? o : "left"
      };
      return /* @__PURE__ */ M.createElement("ul", {
        className: "recharts-default-legend",
        style: u
      }, this.renderItems());
    }
  }]);
})(br);
Ha(tp, "displayName", "Legend");
Ha(tp, "defaultProps", {
  iconSize: 14,
  layout: "horizontal",
  align: "center",
  verticalAlign: "middle",
  inactiveColor: "#ccc"
});
var bs, im;
function FM() {
  if (im) return bs;
  im = 1;
  var e = bu();
  function t() {
    this.__data__ = new e(), this.size = 0;
  }
  return bs = t, bs;
}
var xs, om;
function zM() {
  if (om) return xs;
  om = 1;
  function e(t) {
    var r = this.__data__, n = r.delete(t);
    return this.size = r.size, n;
  }
  return xs = e, xs;
}
var ws, um;
function UM() {
  if (um) return ws;
  um = 1;
  function e(t) {
    return this.__data__.get(t);
  }
  return ws = e, ws;
}
var Os, lm;
function WM() {
  if (lm) return Os;
  lm = 1;
  function e(t) {
    return this.__data__.has(t);
  }
  return Os = e, Os;
}
var _s, sm;
function HM() {
  if (sm) return _s;
  sm = 1;
  var e = bu(), t = Hh(), r = Gh(), n = 200;
  function a(i, o) {
    var u = this.__data__;
    if (u instanceof e) {
      var l = u.__data__;
      if (!t || l.length < n - 1)
        return l.push([i, o]), this.size = ++u.size, this;
      u = this.__data__ = new r(l);
    }
    return u.set(i, o), this.size = u.size, this;
  }
  return _s = a, _s;
}
var Ss, cm;
function kw() {
  if (cm) return Ss;
  cm = 1;
  var e = bu(), t = FM(), r = zM(), n = UM(), a = WM(), i = HM();
  function o(u) {
    var l = this.__data__ = new e(u);
    this.size = l.size;
  }
  return o.prototype.clear = t, o.prototype.delete = r, o.prototype.get = n, o.prototype.has = a, o.prototype.set = i, Ss = o, Ss;
}
var Ps, fm;
function GM() {
  if (fm) return Ps;
  fm = 1;
  var e = "__lodash_hash_undefined__";
  function t(r) {
    return this.__data__.set(r, e), this;
  }
  return Ps = t, Ps;
}
var As, dm;
function KM() {
  if (dm) return As;
  dm = 1;
  function e(t) {
    return this.__data__.has(t);
  }
  return As = e, As;
}
var Es, hm;
function Iw() {
  if (hm) return Es;
  hm = 1;
  var e = Gh(), t = GM(), r = KM();
  function n(a) {
    var i = -1, o = a == null ? 0 : a.length;
    for (this.__data__ = new e(); ++i < o; )
      this.add(a[i]);
  }
  return n.prototype.add = n.prototype.push = t, n.prototype.has = r, Es = n, Es;
}
var Ts, pm;
function Dw() {
  if (pm) return Ts;
  pm = 1;
  function e(t, r) {
    for (var n = -1, a = t == null ? 0 : t.length; ++n < a; )
      if (r(t[n], n, t))
        return !0;
    return !1;
  }
  return Ts = e, Ts;
}
var Ms, vm;
function Lw() {
  if (vm) return Ms;
  vm = 1;
  function e(t, r) {
    return t.has(r);
  }
  return Ms = e, Ms;
}
var js, ym;
function qw() {
  if (ym) return js;
  ym = 1;
  var e = Iw(), t = Dw(), r = Lw(), n = 1, a = 2;
  function i(o, u, l, s, f, c) {
    var d = l & n, h = o.length, y = u.length;
    if (h != y && !(d && y > h))
      return !1;
    var v = c.get(o), p = c.get(u);
    if (v && p)
      return v == u && p == o;
    var g = -1, b = !0, w = l & a ? new e() : void 0;
    for (c.set(o, u), c.set(u, o); ++g < h; ) {
      var _ = o[g], m = u[g];
      if (s)
        var O = d ? s(m, _, g, u, o, c) : s(_, m, g, o, u, c);
      if (O !== void 0) {
        if (O)
          continue;
        b = !1;
        break;
      }
      if (w) {
        if (!t(u, function(x, S) {
          if (!r(w, S) && (_ === x || f(_, x, l, s, c)))
            return w.push(S);
        })) {
          b = !1;
          break;
        }
      } else if (!(_ === m || f(_, m, l, s, c))) {
        b = !1;
        break;
      }
    }
    return c.delete(o), c.delete(u), b;
  }
  return js = i, js;
}
var Ns, mm;
function VM() {
  if (mm) return Ns;
  mm = 1;
  var e = Qt(), t = e.Uint8Array;
  return Ns = t, Ns;
}
var Cs, gm;
function XM() {
  if (gm) return Cs;
  gm = 1;
  function e(t) {
    var r = -1, n = Array(t.size);
    return t.forEach(function(a, i) {
      n[++r] = [i, a];
    }), n;
  }
  return Cs = e, Cs;
}
var $s, bm;
function rp() {
  if (bm) return $s;
  bm = 1;
  function e(t) {
    var r = -1, n = Array(t.size);
    return t.forEach(function(a) {
      n[++r] = a;
    }), n;
  }
  return $s = e, $s;
}
var Rs, xm;
function YM() {
  if (xm) return Rs;
  xm = 1;
  var e = Ni(), t = VM(), r = Wh(), n = qw(), a = XM(), i = rp(), o = 1, u = 2, l = "[object Boolean]", s = "[object Date]", f = "[object Error]", c = "[object Map]", d = "[object Number]", h = "[object RegExp]", y = "[object Set]", v = "[object String]", p = "[object Symbol]", g = "[object ArrayBuffer]", b = "[object DataView]", w = e ? e.prototype : void 0, _ = w ? w.valueOf : void 0;
  function m(O, x, S, T, C, A, N) {
    switch (S) {
      case b:
        if (O.byteLength != x.byteLength || O.byteOffset != x.byteOffset)
          return !1;
        O = O.buffer, x = x.buffer;
      case g:
        return !(O.byteLength != x.byteLength || !A(new t(O), new t(x)));
      case l:
      case s:
      case d:
        return r(+O, +x);
      case f:
        return O.name == x.name && O.message == x.message;
      case h:
      case v:
        return O == x + "";
      case c:
        var $ = a;
      case y:
        var D = T & o;
        if ($ || ($ = i), O.size != x.size && !D)
          return !1;
        var R = N.get(O);
        if (R)
          return R == x;
        T |= u, N.set(O, x);
        var L = n($(O), $(x), T, C, A, N);
        return N.delete(O), L;
      case p:
        if (_)
          return _.call(O) == _.call(x);
    }
    return !1;
  }
  return Rs = m, Rs;
}
var ks, wm;
function Bw() {
  if (wm) return ks;
  wm = 1;
  function e(t, r) {
    for (var n = -1, a = r.length, i = t.length; ++n < a; )
      t[i + n] = r[n];
    return t;
  }
  return ks = e, ks;
}
var Is, Om;
function ZM() {
  if (Om) return Is;
  Om = 1;
  var e = Bw(), t = ht();
  function r(n, a, i) {
    var o = a(n);
    return t(n) ? o : e(o, i(n));
  }
  return Is = r, Is;
}
var Ds, _m;
function JM() {
  if (_m) return Ds;
  _m = 1;
  function e(t, r) {
    for (var n = -1, a = t == null ? 0 : t.length, i = 0, o = []; ++n < a; ) {
      var u = t[n];
      r(u, n, t) && (o[i++] = u);
    }
    return o;
  }
  return Ds = e, Ds;
}
var Ls, Sm;
function QM() {
  if (Sm) return Ls;
  Sm = 1;
  function e() {
    return [];
  }
  return Ls = e, Ls;
}
var qs, Pm;
function ej() {
  if (Pm) return qs;
  Pm = 1;
  var e = JM(), t = QM(), r = Object.prototype, n = r.propertyIsEnumerable, a = Object.getOwnPropertySymbols, i = a ? function(o) {
    return o == null ? [] : (o = Object(o), e(a(o), function(u) {
      return n.call(o, u);
    }));
  } : t;
  return qs = i, qs;
}
var Bs, Am;
function tj() {
  if (Am) return Bs;
  Am = 1;
  function e(t, r) {
    for (var n = -1, a = Array(t); ++n < t; )
      a[n] = r(n);
    return a;
  }
  return Bs = e, Bs;
}
var Fs, Em;
function rj() {
  if (Em) return Fs;
  Em = 1;
  var e = xr(), t = wr(), r = "[object Arguments]";
  function n(a) {
    return t(a) && e(a) == r;
  }
  return Fs = n, Fs;
}
var zs, Tm;
function np() {
  if (Tm) return zs;
  Tm = 1;
  var e = rj(), t = wr(), r = Object.prototype, n = r.hasOwnProperty, a = r.propertyIsEnumerable, i = e(/* @__PURE__ */ (function() {
    return arguments;
  })()) ? e : function(o) {
    return t(o) && n.call(o, "callee") && !a.call(o, "callee");
  };
  return zs = i, zs;
}
var Na = { exports: {} }, Us, Mm;
function nj() {
  if (Mm) return Us;
  Mm = 1;
  function e() {
    return !1;
  }
  return Us = e, Us;
}
Na.exports;
var jm;
function Fw() {
  return jm || (jm = 1, (function(e, t) {
    var r = Qt(), n = nj(), a = t && !t.nodeType && t, i = a && !0 && e && !e.nodeType && e, o = i && i.exports === a, u = o ? r.Buffer : void 0, l = u ? u.isBuffer : void 0, s = l || n;
    e.exports = s;
  })(Na, Na.exports)), Na.exports;
}
var Ws, Nm;
function ap() {
  if (Nm) return Ws;
  Nm = 1;
  var e = 9007199254740991, t = /^(?:0|[1-9]\d*)$/;
  function r(n, a) {
    var i = typeof n;
    return a = a ?? e, !!a && (i == "number" || i != "symbol" && t.test(n)) && n > -1 && n % 1 == 0 && n < a;
  }
  return Ws = r, Ws;
}
var Hs, Cm;
function ip() {
  if (Cm) return Hs;
  Cm = 1;
  var e = 9007199254740991;
  function t(r) {
    return typeof r == "number" && r > -1 && r % 1 == 0 && r <= e;
  }
  return Hs = t, Hs;
}
var Gs, $m;
function aj() {
  if ($m) return Gs;
  $m = 1;
  var e = xr(), t = ip(), r = wr(), n = "[object Arguments]", a = "[object Array]", i = "[object Boolean]", o = "[object Date]", u = "[object Error]", l = "[object Function]", s = "[object Map]", f = "[object Number]", c = "[object Object]", d = "[object RegExp]", h = "[object Set]", y = "[object String]", v = "[object WeakMap]", p = "[object ArrayBuffer]", g = "[object DataView]", b = "[object Float32Array]", w = "[object Float64Array]", _ = "[object Int8Array]", m = "[object Int16Array]", O = "[object Int32Array]", x = "[object Uint8Array]", S = "[object Uint8ClampedArray]", T = "[object Uint16Array]", C = "[object Uint32Array]", A = {};
  A[b] = A[w] = A[_] = A[m] = A[O] = A[x] = A[S] = A[T] = A[C] = !0, A[n] = A[a] = A[p] = A[i] = A[g] = A[o] = A[u] = A[l] = A[s] = A[f] = A[c] = A[d] = A[h] = A[y] = A[v] = !1;
  function N($) {
    return r($) && t($.length) && !!A[e($)];
  }
  return Gs = N, Gs;
}
var Ks, Rm;
function zw() {
  if (Rm) return Ks;
  Rm = 1;
  function e(t) {
    return function(r) {
      return t(r);
    };
  }
  return Ks = e, Ks;
}
var Ca = { exports: {} };
Ca.exports;
var km;
function ij() {
  return km || (km = 1, (function(e, t) {
    var r = lw(), n = t && !t.nodeType && t, a = n && !0 && e && !e.nodeType && e, i = a && a.exports === n, o = i && r.process, u = (function() {
      try {
        var l = a && a.require && a.require("util").types;
        return l || o && o.binding && o.binding("util");
      } catch {
      }
    })();
    e.exports = u;
  })(Ca, Ca.exports)), Ca.exports;
}
var Vs, Im;
function Uw() {
  if (Im) return Vs;
  Im = 1;
  var e = aj(), t = zw(), r = ij(), n = r && r.isTypedArray, a = n ? t(n) : e;
  return Vs = a, Vs;
}
var Xs, Dm;
function oj() {
  if (Dm) return Xs;
  Dm = 1;
  var e = tj(), t = np(), r = ht(), n = Fw(), a = ap(), i = Uw(), o = Object.prototype, u = o.hasOwnProperty;
  function l(s, f) {
    var c = r(s), d = !c && t(s), h = !c && !d && n(s), y = !c && !d && !h && i(s), v = c || d || h || y, p = v ? e(s.length, String) : [], g = p.length;
    for (var b in s)
      (f || u.call(s, b)) && !(v && // Safari 9 has enumerable `arguments.length` in strict mode.
      (b == "length" || // Node.js 0.10 has enumerable non-index properties on buffers.
      h && (b == "offset" || b == "parent") || // PhantomJS 2 has enumerable non-index properties on typed arrays.
      y && (b == "buffer" || b == "byteLength" || b == "byteOffset") || // Skip index properties.
      a(b, g))) && p.push(b);
    return p;
  }
  return Xs = l, Xs;
}
var Ys, Lm;
function uj() {
  if (Lm) return Ys;
  Lm = 1;
  var e = Object.prototype;
  function t(r) {
    var n = r && r.constructor, a = typeof n == "function" && n.prototype || e;
    return r === a;
  }
  return Ys = t, Ys;
}
var Zs, qm;
function Ww() {
  if (qm) return Zs;
  qm = 1;
  function e(t, r) {
    return function(n) {
      return t(r(n));
    };
  }
  return Zs = e, Zs;
}
var Js, Bm;
function lj() {
  if (Bm) return Js;
  Bm = 1;
  var e = Ww(), t = e(Object.keys, Object);
  return Js = t, Js;
}
var Qs, Fm;
function sj() {
  if (Fm) return Qs;
  Fm = 1;
  var e = uj(), t = lj(), r = Object.prototype, n = r.hasOwnProperty;
  function a(i) {
    if (!e(i))
      return t(i);
    var o = [];
    for (var u in Object(i))
      n.call(i, u) && u != "constructor" && o.push(u);
    return o;
  }
  return Qs = a, Qs;
}
var ec, zm;
function Ri() {
  if (zm) return ec;
  zm = 1;
  var e = Uh(), t = ip();
  function r(n) {
    return n != null && t(n.length) && !e(n);
  }
  return ec = r, ec;
}
var tc, Um;
function Au() {
  if (Um) return tc;
  Um = 1;
  var e = oj(), t = sj(), r = Ri();
  function n(a) {
    return r(a) ? e(a) : t(a);
  }
  return tc = n, tc;
}
var rc, Wm;
function cj() {
  if (Wm) return rc;
  Wm = 1;
  var e = ZM(), t = ej(), r = Au();
  function n(a) {
    return e(a, r, t);
  }
  return rc = n, rc;
}
var nc, Hm;
function fj() {
  if (Hm) return nc;
  Hm = 1;
  var e = cj(), t = 1, r = Object.prototype, n = r.hasOwnProperty;
  function a(i, o, u, l, s, f) {
    var c = u & t, d = e(i), h = d.length, y = e(o), v = y.length;
    if (h != v && !c)
      return !1;
    for (var p = h; p--; ) {
      var g = d[p];
      if (!(c ? g in o : n.call(o, g)))
        return !1;
    }
    var b = f.get(i), w = f.get(o);
    if (b && w)
      return b == o && w == i;
    var _ = !0;
    f.set(i, o), f.set(o, i);
    for (var m = c; ++p < h; ) {
      g = d[p];
      var O = i[g], x = o[g];
      if (l)
        var S = c ? l(x, O, g, o, i, f) : l(O, x, g, i, o, f);
      if (!(S === void 0 ? O === x || s(O, x, u, l, f) : S)) {
        _ = !1;
        break;
      }
      m || (m = g == "constructor");
    }
    if (_ && !m) {
      var T = i.constructor, C = o.constructor;
      T != C && "constructor" in i && "constructor" in o && !(typeof T == "function" && T instanceof T && typeof C == "function" && C instanceof C) && (_ = !1);
    }
    return f.delete(i), f.delete(o), _;
  }
  return nc = a, nc;
}
var ac, Gm;
function dj() {
  if (Gm) return ac;
  Gm = 1;
  var e = vn(), t = Qt(), r = e(t, "DataView");
  return ac = r, ac;
}
var ic, Km;
function hj() {
  if (Km) return ic;
  Km = 1;
  var e = vn(), t = Qt(), r = e(t, "Promise");
  return ic = r, ic;
}
var oc, Vm;
function Hw() {
  if (Vm) return oc;
  Vm = 1;
  var e = vn(), t = Qt(), r = e(t, "Set");
  return oc = r, oc;
}
var uc, Xm;
function pj() {
  if (Xm) return uc;
  Xm = 1;
  var e = vn(), t = Qt(), r = e(t, "WeakMap");
  return uc = r, uc;
}
var lc, Ym;
function vj() {
  if (Ym) return lc;
  Ym = 1;
  var e = dj(), t = Hh(), r = hj(), n = Hw(), a = pj(), i = xr(), o = sw(), u = "[object Map]", l = "[object Object]", s = "[object Promise]", f = "[object Set]", c = "[object WeakMap]", d = "[object DataView]", h = o(e), y = o(t), v = o(r), p = o(n), g = o(a), b = i;
  return (e && b(new e(new ArrayBuffer(1))) != d || t && b(new t()) != u || r && b(r.resolve()) != s || n && b(new n()) != f || a && b(new a()) != c) && (b = function(w) {
    var _ = i(w), m = _ == l ? w.constructor : void 0, O = m ? o(m) : "";
    if (O)
      switch (O) {
        case h:
          return d;
        case y:
          return u;
        case v:
          return s;
        case p:
          return f;
        case g:
          return c;
      }
    return _;
  }), lc = b, lc;
}
var sc, Zm;
function yj() {
  if (Zm) return sc;
  Zm = 1;
  var e = kw(), t = qw(), r = YM(), n = fj(), a = vj(), i = ht(), o = Fw(), u = Uw(), l = 1, s = "[object Arguments]", f = "[object Array]", c = "[object Object]", d = Object.prototype, h = d.hasOwnProperty;
  function y(v, p, g, b, w, _) {
    var m = i(v), O = i(p), x = m ? f : a(v), S = O ? f : a(p);
    x = x == s ? c : x, S = S == s ? c : S;
    var T = x == c, C = S == c, A = x == S;
    if (A && o(v)) {
      if (!o(p))
        return !1;
      m = !0, T = !1;
    }
    if (A && !T)
      return _ || (_ = new e()), m || u(v) ? t(v, p, g, b, w, _) : r(v, p, x, g, b, w, _);
    if (!(g & l)) {
      var N = T && h.call(v, "__wrapped__"), $ = C && h.call(p, "__wrapped__");
      if (N || $) {
        var D = N ? v.value() : v, R = $ ? p.value() : p;
        return _ || (_ = new e()), w(D, R, g, b, _);
      }
    }
    return A ? (_ || (_ = new e()), n(v, p, g, b, w, _)) : !1;
  }
  return sc = y, sc;
}
var cc, Jm;
function op() {
  if (Jm) return cc;
  Jm = 1;
  var e = yj(), t = wr();
  function r(n, a, i, o, u) {
    return n === a ? !0 : n == null || a == null || !t(n) && !t(a) ? n !== n && a !== a : e(n, a, i, o, r, u);
  }
  return cc = r, cc;
}
var fc, Qm;
function mj() {
  if (Qm) return fc;
  Qm = 1;
  var e = kw(), t = op(), r = 1, n = 2;
  function a(i, o, u, l) {
    var s = u.length, f = s, c = !l;
    if (i == null)
      return !f;
    for (i = Object(i); s--; ) {
      var d = u[s];
      if (c && d[2] ? d[1] !== i[d[0]] : !(d[0] in i))
        return !1;
    }
    for (; ++s < f; ) {
      d = u[s];
      var h = d[0], y = i[h], v = d[1];
      if (c && d[2]) {
        if (y === void 0 && !(h in i))
          return !1;
      } else {
        var p = new e();
        if (l)
          var g = l(y, v, h, i, o, p);
        if (!(g === void 0 ? t(v, y, r | n, l, p) : g))
          return !1;
      }
    }
    return !0;
  }
  return fc = a, fc;
}
var dc, eg;
function Gw() {
  if (eg) return dc;
  eg = 1;
  var e = Lr();
  function t(r) {
    return r === r && !e(r);
  }
  return dc = t, dc;
}
var hc, tg;
function gj() {
  if (tg) return hc;
  tg = 1;
  var e = Gw(), t = Au();
  function r(n) {
    for (var a = t(n), i = a.length; i--; ) {
      var o = a[i], u = n[o];
      a[i] = [o, u, e(u)];
    }
    return a;
  }
  return hc = r, hc;
}
var pc, rg;
function Kw() {
  if (rg) return pc;
  rg = 1;
  function e(t, r) {
    return function(n) {
      return n == null ? !1 : n[t] === r && (r !== void 0 || t in Object(n));
    };
  }
  return pc = e, pc;
}
var vc, ng;
function bj() {
  if (ng) return vc;
  ng = 1;
  var e = mj(), t = gj(), r = Kw();
  function n(a) {
    var i = t(a);
    return i.length == 1 && i[0][2] ? r(i[0][0], i[0][1]) : function(o) {
      return o === a || e(o, a, i);
    };
  }
  return vc = n, vc;
}
var yc, ag;
function xj() {
  if (ag) return yc;
  ag = 1;
  function e(t, r) {
    return t != null && r in Object(t);
  }
  return yc = e, yc;
}
var mc, ig;
function wj() {
  if (ig) return mc;
  ig = 1;
  var e = dw(), t = np(), r = ht(), n = ap(), a = ip(), i = wu();
  function o(u, l, s) {
    l = e(l, u);
    for (var f = -1, c = l.length, d = !1; ++f < c; ) {
      var h = i(l[f]);
      if (!(d = u != null && s(u, h)))
        break;
      u = u[h];
    }
    return d || ++f != c ? d : (c = u == null ? 0 : u.length, !!c && a(c) && n(h, c) && (r(u) || t(u)));
  }
  return mc = o, mc;
}
var gc, og;
function Oj() {
  if (og) return gc;
  og = 1;
  var e = xj(), t = wj();
  function r(n, a) {
    return n != null && t(n, a, e);
  }
  return gc = r, gc;
}
var bc, ug;
function _j() {
  if (ug) return bc;
  ug = 1;
  var e = op(), t = hw(), r = Oj(), n = zh(), a = Gw(), i = Kw(), o = wu(), u = 1, l = 2;
  function s(f, c) {
    return n(f) && a(c) ? i(o(f), c) : function(d) {
      var h = t(d, f);
      return h === void 0 && h === c ? r(d, f) : e(c, h, u | l);
    };
  }
  return bc = s, bc;
}
var xc, lg;
function oa() {
  if (lg) return xc;
  lg = 1;
  function e(t) {
    return t;
  }
  return xc = e, xc;
}
var wc, sg;
function Sj() {
  if (sg) return wc;
  sg = 1;
  function e(t) {
    return function(r) {
      return r?.[t];
    };
  }
  return wc = e, wc;
}
var Oc, cg;
function Pj() {
  if (cg) return Oc;
  cg = 1;
  var e = Vh();
  function t(r) {
    return function(n) {
      return e(n, r);
    };
  }
  return Oc = t, Oc;
}
var _c, fg;
function Aj() {
  if (fg) return _c;
  fg = 1;
  var e = Sj(), t = Pj(), r = zh(), n = wu();
  function a(i) {
    return r(i) ? e(n(i)) : t(i);
  }
  return _c = a, _c;
}
var Sc, dg;
function qr() {
  if (dg) return Sc;
  dg = 1;
  var e = bj(), t = _j(), r = oa(), n = ht(), a = Aj();
  function i(o) {
    return typeof o == "function" ? o : o == null ? r : typeof o == "object" ? n(o) ? t(o[0], o[1]) : e(o) : a(o);
  }
  return Sc = i, Sc;
}
var Pc, hg;
function Vw() {
  if (hg) return Pc;
  hg = 1;
  function e(t, r, n, a) {
    for (var i = t.length, o = n + (a ? 1 : -1); a ? o-- : ++o < i; )
      if (r(t[o], o, t))
        return o;
    return -1;
  }
  return Pc = e, Pc;
}
var Ac, pg;
function Ej() {
  if (pg) return Ac;
  pg = 1;
  function e(t) {
    return t !== t;
  }
  return Ac = e, Ac;
}
var Ec, vg;
function Tj() {
  if (vg) return Ec;
  vg = 1;
  function e(t, r, n) {
    for (var a = n - 1, i = t.length; ++a < i; )
      if (t[a] === r)
        return a;
    return -1;
  }
  return Ec = e, Ec;
}
var Tc, yg;
function Mj() {
  if (yg) return Tc;
  yg = 1;
  var e = Vw(), t = Ej(), r = Tj();
  function n(a, i, o) {
    return i === i ? r(a, i, o) : e(a, t, o);
  }
  return Tc = n, Tc;
}
var Mc, mg;
function jj() {
  if (mg) return Mc;
  mg = 1;
  var e = Mj();
  function t(r, n) {
    var a = r == null ? 0 : r.length;
    return !!a && e(r, n, 0) > -1;
  }
  return Mc = t, Mc;
}
var jc, gg;
function Nj() {
  if (gg) return jc;
  gg = 1;
  function e(t, r, n) {
    for (var a = -1, i = t == null ? 0 : t.length; ++a < i; )
      if (n(r, t[a]))
        return !0;
    return !1;
  }
  return jc = e, jc;
}
var Nc, bg;
function Cj() {
  if (bg) return Nc;
  bg = 1;
  function e() {
  }
  return Nc = e, Nc;
}
var Cc, xg;
function $j() {
  if (xg) return Cc;
  xg = 1;
  var e = Hw(), t = Cj(), r = rp(), n = 1 / 0, a = e && 1 / r(new e([, -0]))[1] == n ? function(i) {
    return new e(i);
  } : t;
  return Cc = a, Cc;
}
var $c, wg;
function Rj() {
  if (wg) return $c;
  wg = 1;
  var e = Iw(), t = jj(), r = Nj(), n = Lw(), a = $j(), i = rp(), o = 200;
  function u(l, s, f) {
    var c = -1, d = t, h = l.length, y = !0, v = [], p = v;
    if (f)
      y = !1, d = r;
    else if (h >= o) {
      var g = s ? null : a(l);
      if (g)
        return i(g);
      y = !1, d = n, p = new e();
    } else
      p = s ? [] : v;
    e:
      for (; ++c < h; ) {
        var b = l[c], w = s ? s(b) : b;
        if (b = f || b !== 0 ? b : 0, y && w === w) {
          for (var _ = p.length; _--; )
            if (p[_] === w)
              continue e;
          s && p.push(w), v.push(b);
        } else d(p, w, f) || (p !== v && p.push(w), v.push(b));
      }
    return v;
  }
  return $c = u, $c;
}
var Rc, Og;
function kj() {
  if (Og) return Rc;
  Og = 1;
  var e = qr(), t = Rj();
  function r(n, a) {
    return n && n.length ? t(n, e(a, 2)) : [];
  }
  return Rc = r, Rc;
}
var Ij = kj();
const _g = /* @__PURE__ */ $e(Ij);
function Xw(e, t, r) {
  return t === !0 ? _g(e, r) : fe(t) ? _g(e, t) : e;
}
function Dn(e) {
  "@babel/helpers - typeof";
  return Dn = typeof Symbol == "function" && typeof Symbol.iterator == "symbol" ? function(t) {
    return typeof t;
  } : function(t) {
    return t && typeof Symbol == "function" && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t;
  }, Dn(e);
}
var Dj = ["ref"];
function Sg(e, t) {
  var r = Object.keys(e);
  if (Object.getOwnPropertySymbols) {
    var n = Object.getOwnPropertySymbols(e);
    t && (n = n.filter(function(a) {
      return Object.getOwnPropertyDescriptor(e, a).enumerable;
    })), r.push.apply(r, n);
  }
  return r;
}
function or(e) {
  for (var t = 1; t < arguments.length; t++) {
    var r = arguments[t] != null ? arguments[t] : {};
    t % 2 ? Sg(Object(r), !0).forEach(function(n) {
      Eu(e, n, r[n]);
    }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(r)) : Sg(Object(r)).forEach(function(n) {
      Object.defineProperty(e, n, Object.getOwnPropertyDescriptor(r, n));
    });
  }
  return e;
}
function Lj(e, t) {
  if (!(e instanceof t))
    throw new TypeError("Cannot call a class as a function");
}
function Pg(e, t) {
  for (var r = 0; r < t.length; r++) {
    var n = t[r];
    n.enumerable = n.enumerable || !1, n.configurable = !0, "value" in n && (n.writable = !0), Object.defineProperty(e, Zw(n.key), n);
  }
}
function qj(e, t, r) {
  return t && Pg(e.prototype, t), r && Pg(e, r), Object.defineProperty(e, "prototype", { writable: !1 }), e;
}
function Bj(e, t, r) {
  return t = Po(t), Fj(e, Yw() ? Reflect.construct(t, r || [], Po(e).constructor) : t.apply(e, r));
}
function Fj(e, t) {
  if (t && (Dn(t) === "object" || typeof t == "function"))
    return t;
  if (t !== void 0)
    throw new TypeError("Derived constructors may only return object or undefined");
  return zj(e);
}
function zj(e) {
  if (e === void 0)
    throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
  return e;
}
function Yw() {
  try {
    var e = !Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function() {
    }));
  } catch {
  }
  return (Yw = function() {
    return !!e;
  })();
}
function Po(e) {
  return Po = Object.setPrototypeOf ? Object.getPrototypeOf.bind() : function(r) {
    return r.__proto__ || Object.getPrototypeOf(r);
  }, Po(e);
}
function Uj(e, t) {
  if (typeof t != "function" && t !== null)
    throw new TypeError("Super expression must either be null or a function");
  e.prototype = Object.create(t && t.prototype, { constructor: { value: e, writable: !0, configurable: !0 } }), Object.defineProperty(e, "prototype", { writable: !1 }), t && Od(e, t);
}
function Od(e, t) {
  return Od = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function(n, a) {
    return n.__proto__ = a, n;
  }, Od(e, t);
}
function Eu(e, t, r) {
  return t = Zw(t), t in e ? Object.defineProperty(e, t, { value: r, enumerable: !0, configurable: !0, writable: !0 }) : e[t] = r, e;
}
function Zw(e) {
  var t = Wj(e, "string");
  return Dn(t) == "symbol" ? t : t + "";
}
function Wj(e, t) {
  if (Dn(e) != "object" || !e) return e;
  var r = e[Symbol.toPrimitive];
  if (r !== void 0) {
    var n = r.call(e, t);
    if (Dn(n) != "object") return n;
    throw new TypeError("@@toPrimitive must return a primitive value.");
  }
  return String(e);
}
function Hj(e, t) {
  if (e == null) return {};
  var r = Gj(e, t), n, a;
  if (Object.getOwnPropertySymbols) {
    var i = Object.getOwnPropertySymbols(e);
    for (a = 0; a < i.length; a++)
      n = i[a], !(t.indexOf(n) >= 0) && Object.prototype.propertyIsEnumerable.call(e, n) && (r[n] = e[n]);
  }
  return r;
}
function Gj(e, t) {
  if (e == null) return {};
  var r = {};
  for (var n in e)
    if (Object.prototype.hasOwnProperty.call(e, n)) {
      if (t.indexOf(n) >= 0) continue;
      r[n] = e[n];
    }
  return r;
}
function Kj(e) {
  return e.value;
}
function Vj(e, t) {
  if (/* @__PURE__ */ M.isValidElement(e))
    return /* @__PURE__ */ M.cloneElement(e, t);
  if (typeof e == "function")
    return /* @__PURE__ */ M.createElement(e, t);
  t.ref;
  var r = Hj(t, Dj);
  return /* @__PURE__ */ M.createElement(tp, r);
}
var Ag = 1, rn = /* @__PURE__ */ (function(e) {
  function t() {
    var r;
    Lj(this, t);
    for (var n = arguments.length, a = new Array(n), i = 0; i < n; i++)
      a[i] = arguments[i];
    return r = Bj(this, t, [].concat(a)), Eu(r, "lastBoundingBox", {
      width: -1,
      height: -1
    }), r;
  }
  return Uj(t, e), qj(t, [{
    key: "componentDidMount",
    value: function() {
      this.updateBBox();
    }
  }, {
    key: "componentDidUpdate",
    value: function() {
      this.updateBBox();
    }
  }, {
    key: "getBBox",
    value: function() {
      if (this.wrapperNode && this.wrapperNode.getBoundingClientRect) {
        var n = this.wrapperNode.getBoundingClientRect();
        return n.height = this.wrapperNode.offsetHeight, n.width = this.wrapperNode.offsetWidth, n;
      }
      return null;
    }
  }, {
    key: "updateBBox",
    value: function() {
      var n = this.props.onBBoxUpdate, a = this.getBBox();
      a ? (Math.abs(a.width - this.lastBoundingBox.width) > Ag || Math.abs(a.height - this.lastBoundingBox.height) > Ag) && (this.lastBoundingBox.width = a.width, this.lastBoundingBox.height = a.height, n && n(a)) : (this.lastBoundingBox.width !== -1 || this.lastBoundingBox.height !== -1) && (this.lastBoundingBox.width = -1, this.lastBoundingBox.height = -1, n && n(null));
    }
  }, {
    key: "getBBoxSnapshot",
    value: function() {
      return this.lastBoundingBox.width >= 0 && this.lastBoundingBox.height >= 0 ? or({}, this.lastBoundingBox) : {
        width: 0,
        height: 0
      };
    }
  }, {
    key: "getDefaultPosition",
    value: function(n) {
      var a = this.props, i = a.layout, o = a.align, u = a.verticalAlign, l = a.margin, s = a.chartWidth, f = a.chartHeight, c, d;
      if (!n || (n.left === void 0 || n.left === null) && (n.right === void 0 || n.right === null))
        if (o === "center" && i === "vertical") {
          var h = this.getBBoxSnapshot();
          c = {
            left: ((s || 0) - h.width) / 2
          };
        } else
          c = o === "right" ? {
            right: l && l.right || 0
          } : {
            left: l && l.left || 0
          };
      if (!n || (n.top === void 0 || n.top === null) && (n.bottom === void 0 || n.bottom === null))
        if (u === "middle") {
          var y = this.getBBoxSnapshot();
          d = {
            top: ((f || 0) - y.height) / 2
          };
        } else
          d = u === "bottom" ? {
            bottom: l && l.bottom || 0
          } : {
            top: l && l.top || 0
          };
      return or(or({}, c), d);
    }
  }, {
    key: "render",
    value: function() {
      var n = this, a = this.props, i = a.content, o = a.width, u = a.height, l = a.wrapperStyle, s = a.payloadUniqBy, f = a.payload, c = or(or({
        position: "absolute",
        width: o || "auto",
        height: u || "auto"
      }, this.getDefaultPosition(l)), l);
      return /* @__PURE__ */ M.createElement("div", {
        className: "recharts-legend-wrapper",
        style: c,
        ref: function(h) {
          n.wrapperNode = h;
        }
      }, Vj(i, or(or({}, this.props), {}, {
        payload: Xw(f, s, Kj)
      })));
    }
  }], [{
    key: "getWithHeight",
    value: function(n, a) {
      var i = or(or({}, this.defaultProps), n.props), o = i.layout;
      return o === "vertical" && H(n.props.height) ? {
        height: n.props.height
      } : o === "horizontal" ? {
        width: n.props.width || a
      } : null;
    }
  }]);
})(br);
Eu(rn, "displayName", "Legend");
Eu(rn, "defaultProps", {
  iconSize: 14,
  layout: "horizontal",
  align: "center",
  verticalAlign: "bottom"
});
var kc, Eg;
function Xj() {
  if (Eg) return kc;
  Eg = 1;
  var e = Ni(), t = np(), r = ht(), n = e ? e.isConcatSpreadable : void 0;
  function a(i) {
    return r(i) || t(i) || !!(n && i && i[n]);
  }
  return kc = a, kc;
}
var Ic, Tg;
function Jw() {
  if (Tg) return Ic;
  Tg = 1;
  var e = Bw(), t = Xj();
  function r(n, a, i, o, u) {
    var l = -1, s = n.length;
    for (i || (i = t), u || (u = []); ++l < s; ) {
      var f = n[l];
      a > 0 && i(f) ? a > 1 ? r(f, a - 1, i, o, u) : e(u, f) : o || (u[u.length] = f);
    }
    return u;
  }
  return Ic = r, Ic;
}
var Dc, Mg;
function Yj() {
  if (Mg) return Dc;
  Mg = 1;
  function e(t) {
    return function(r, n, a) {
      for (var i = -1, o = Object(r), u = a(r), l = u.length; l--; ) {
        var s = u[t ? l : ++i];
        if (n(o[s], s, o) === !1)
          break;
      }
      return r;
    };
  }
  return Dc = e, Dc;
}
var Lc, jg;
function Zj() {
  if (jg) return Lc;
  jg = 1;
  var e = Yj(), t = e();
  return Lc = t, Lc;
}
var qc, Ng;
function Qw() {
  if (Ng) return qc;
  Ng = 1;
  var e = Zj(), t = Au();
  function r(n, a) {
    return n && e(n, a, t);
  }
  return qc = r, qc;
}
var Bc, Cg;
function Jj() {
  if (Cg) return Bc;
  Cg = 1;
  var e = Ri();
  function t(r, n) {
    return function(a, i) {
      if (a == null)
        return a;
      if (!e(a))
        return r(a, i);
      for (var o = a.length, u = n ? o : -1, l = Object(a); (n ? u-- : ++u < o) && i(l[u], u, l) !== !1; )
        ;
      return a;
    };
  }
  return Bc = t, Bc;
}
var Fc, $g;
function up() {
  if ($g) return Fc;
  $g = 1;
  var e = Qw(), t = Jj(), r = t(e);
  return Fc = r, Fc;
}
var zc, Rg;
function eO() {
  if (Rg) return zc;
  Rg = 1;
  var e = up(), t = Ri();
  function r(n, a) {
    var i = -1, o = t(n) ? Array(n.length) : [];
    return e(n, function(u, l, s) {
      o[++i] = a(u, l, s);
    }), o;
  }
  return zc = r, zc;
}
var Uc, kg;
function Qj() {
  if (kg) return Uc;
  kg = 1;
  function e(t, r) {
    var n = t.length;
    for (t.sort(r); n--; )
      t[n] = t[n].value;
    return t;
  }
  return Uc = e, Uc;
}
var Wc, Ig;
function eN() {
  if (Ig) return Wc;
  Ig = 1;
  var e = na();
  function t(r, n) {
    if (r !== n) {
      var a = r !== void 0, i = r === null, o = r === r, u = e(r), l = n !== void 0, s = n === null, f = n === n, c = e(n);
      if (!s && !c && !u && r > n || u && l && f && !s && !c || i && l && f || !a && f || !o)
        return 1;
      if (!i && !u && !c && r < n || c && a && o && !i && !u || s && a && o || !l && o || !f)
        return -1;
    }
    return 0;
  }
  return Wc = t, Wc;
}
var Hc, Dg;
function tN() {
  if (Dg) return Hc;
  Dg = 1;
  var e = eN();
  function t(r, n, a) {
    for (var i = -1, o = r.criteria, u = n.criteria, l = o.length, s = a.length; ++i < l; ) {
      var f = e(o[i], u[i]);
      if (f) {
        if (i >= s)
          return f;
        var c = a[i];
        return f * (c == "desc" ? -1 : 1);
      }
    }
    return r.index - n.index;
  }
  return Hc = t, Hc;
}
var Gc, Lg;
function rN() {
  if (Lg) return Gc;
  Lg = 1;
  var e = Kh(), t = Vh(), r = qr(), n = eO(), a = Qj(), i = zw(), o = tN(), u = oa(), l = ht();
  function s(f, c, d) {
    c.length ? c = e(c, function(v) {
      return l(v) ? function(p) {
        return t(p, v.length === 1 ? v[0] : v);
      } : v;
    }) : c = [u];
    var h = -1;
    c = e(c, i(r));
    var y = n(f, function(v, p, g) {
      var b = e(c, function(w) {
        return w(v);
      });
      return { criteria: b, index: ++h, value: v };
    });
    return a(y, function(v, p) {
      return o(v, p, d);
    });
  }
  return Gc = s, Gc;
}
var Kc, qg;
function nN() {
  if (qg) return Kc;
  qg = 1;
  function e(t, r, n) {
    switch (n.length) {
      case 0:
        return t.call(r);
      case 1:
        return t.call(r, n[0]);
      case 2:
        return t.call(r, n[0], n[1]);
      case 3:
        return t.call(r, n[0], n[1], n[2]);
    }
    return t.apply(r, n);
  }
  return Kc = e, Kc;
}
var Vc, Bg;
function aN() {
  if (Bg) return Vc;
  Bg = 1;
  var e = nN(), t = Math.max;
  function r(n, a, i) {
    return a = t(a === void 0 ? n.length - 1 : a, 0), function() {
      for (var o = arguments, u = -1, l = t(o.length - a, 0), s = Array(l); ++u < l; )
        s[u] = o[a + u];
      u = -1;
      for (var f = Array(a + 1); ++u < a; )
        f[u] = o[u];
      return f[a] = i(s), e(n, this, f);
    };
  }
  return Vc = r, Vc;
}
var Xc, Fg;
function iN() {
  if (Fg) return Xc;
  Fg = 1;
  function e(t) {
    return function() {
      return t;
    };
  }
  return Xc = e, Xc;
}
var Yc, zg;
function tO() {
  if (zg) return Yc;
  zg = 1;
  var e = vn(), t = (function() {
    try {
      var r = e(Object, "defineProperty");
      return r({}, "", {}), r;
    } catch {
    }
  })();
  return Yc = t, Yc;
}
var Zc, Ug;
function oN() {
  if (Ug) return Zc;
  Ug = 1;
  var e = iN(), t = tO(), r = oa(), n = t ? function(a, i) {
    return t(a, "toString", {
      configurable: !0,
      enumerable: !1,
      value: e(i),
      writable: !0
    });
  } : r;
  return Zc = n, Zc;
}
var Jc, Wg;
function uN() {
  if (Wg) return Jc;
  Wg = 1;
  var e = 800, t = 16, r = Date.now;
  function n(a) {
    var i = 0, o = 0;
    return function() {
      var u = r(), l = t - (u - o);
      if (o = u, l > 0) {
        if (++i >= e)
          return arguments[0];
      } else
        i = 0;
      return a.apply(void 0, arguments);
    };
  }
  return Jc = n, Jc;
}
var Qc, Hg;
function lN() {
  if (Hg) return Qc;
  Hg = 1;
  var e = oN(), t = uN(), r = t(e);
  return Qc = r, Qc;
}
var ef, Gg;
function sN() {
  if (Gg) return ef;
  Gg = 1;
  var e = oa(), t = aN(), r = lN();
  function n(a, i) {
    return r(t(a, i, e), a + "");
  }
  return ef = n, ef;
}
var tf, Kg;
function Tu() {
  if (Kg) return tf;
  Kg = 1;
  var e = Wh(), t = Ri(), r = ap(), n = Lr();
  function a(i, o, u) {
    if (!n(u))
      return !1;
    var l = typeof o;
    return (l == "number" ? t(u) && r(o, u.length) : l == "string" && o in u) ? e(u[o], i) : !1;
  }
  return tf = a, tf;
}
var rf, Vg;
function cN() {
  if (Vg) return rf;
  Vg = 1;
  var e = Jw(), t = rN(), r = sN(), n = Tu(), a = r(function(i, o) {
    if (i == null)
      return [];
    var u = o.length;
    return u > 1 && n(i, o[0], o[1]) ? o = [] : u > 2 && n(o[0], o[1], o[2]) && (o = [o[0]]), t(i, e(o, 1), []);
  });
  return rf = a, rf;
}
var fN = cN();
const lp = /* @__PURE__ */ $e(fN);
function Ga(e) {
  "@babel/helpers - typeof";
  return Ga = typeof Symbol == "function" && typeof Symbol.iterator == "symbol" ? function(t) {
    return typeof t;
  } : function(t) {
    return t && typeof Symbol == "function" && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t;
  }, Ga(e);
}
function _d() {
  return _d = Object.assign ? Object.assign.bind() : function(e) {
    for (var t = 1; t < arguments.length; t++) {
      var r = arguments[t];
      for (var n in r)
        Object.prototype.hasOwnProperty.call(r, n) && (e[n] = r[n]);
    }
    return e;
  }, _d.apply(this, arguments);
}
function dN(e, t) {
  return yN(e) || vN(e, t) || pN(e, t) || hN();
}
function hN() {
  throw new TypeError(`Invalid attempt to destructure non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`);
}
function pN(e, t) {
  if (e) {
    if (typeof e == "string") return Xg(e, t);
    var r = Object.prototype.toString.call(e).slice(8, -1);
    if (r === "Object" && e.constructor && (r = e.constructor.name), r === "Map" || r === "Set") return Array.from(e);
    if (r === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r)) return Xg(e, t);
  }
}
function Xg(e, t) {
  (t == null || t > e.length) && (t = e.length);
  for (var r = 0, n = new Array(t); r < t; r++) n[r] = e[r];
  return n;
}
function vN(e, t) {
  var r = e == null ? null : typeof Symbol < "u" && e[Symbol.iterator] || e["@@iterator"];
  if (r != null) {
    var n, a, i, o, u = [], l = !0, s = !1;
    try {
      if (i = (r = r.call(e)).next, t !== 0) for (; !(l = (n = i.call(r)).done) && (u.push(n.value), u.length !== t); l = !0) ;
    } catch (f) {
      s = !0, a = f;
    } finally {
      try {
        if (!l && r.return != null && (o = r.return(), Object(o) !== o)) return;
      } finally {
        if (s) throw a;
      }
    }
    return u;
  }
}
function yN(e) {
  if (Array.isArray(e)) return e;
}
function Yg(e, t) {
  var r = Object.keys(e);
  if (Object.getOwnPropertySymbols) {
    var n = Object.getOwnPropertySymbols(e);
    t && (n = n.filter(function(a) {
      return Object.getOwnPropertyDescriptor(e, a).enumerable;
    })), r.push.apply(r, n);
  }
  return r;
}
function nf(e) {
  for (var t = 1; t < arguments.length; t++) {
    var r = arguments[t] != null ? arguments[t] : {};
    t % 2 ? Yg(Object(r), !0).forEach(function(n) {
      mN(e, n, r[n]);
    }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(r)) : Yg(Object(r)).forEach(function(n) {
      Object.defineProperty(e, n, Object.getOwnPropertyDescriptor(r, n));
    });
  }
  return e;
}
function mN(e, t, r) {
  return t = gN(t), t in e ? Object.defineProperty(e, t, { value: r, enumerable: !0, configurable: !0, writable: !0 }) : e[t] = r, e;
}
function gN(e) {
  var t = bN(e, "string");
  return Ga(t) == "symbol" ? t : t + "";
}
function bN(e, t) {
  if (Ga(e) != "object" || !e) return e;
  var r = e[Symbol.toPrimitive];
  if (r !== void 0) {
    var n = r.call(e, t);
    if (Ga(n) != "object") return n;
    throw new TypeError("@@toPrimitive must return a primitive value.");
  }
  return (t === "string" ? String : Number)(e);
}
function xN(e) {
  return Array.isArray(e) && Ve(e[0]) && Ve(e[1]) ? e.join(" ~ ") : e;
}
var wN = function(t) {
  var r = t.separator, n = r === void 0 ? " : " : r, a = t.contentStyle, i = a === void 0 ? {} : a, o = t.itemStyle, u = o === void 0 ? {} : o, l = t.labelStyle, s = l === void 0 ? {} : l, f = t.payload, c = t.formatter, d = t.itemSorter, h = t.wrapperClassName, y = t.labelClassName, v = t.label, p = t.labelFormatter, g = t.accessibilityLayer, b = g === void 0 ? !1 : g, w = function() {
    if (f && f.length) {
      var N = {
        padding: 0,
        margin: 0
      }, $ = (d ? lp(f, d) : f).map(function(D, R) {
        if (D.type === "none")
          return null;
        var L = nf({
          display: "block",
          paddingTop: 4,
          paddingBottom: 4,
          color: D.color || "#000"
        }, u), z = D.formatter || c || xN, F = D.value, W = D.name, X = F, J = W;
        if (z && X != null && J != null) {
          var G = z(F, W, D, R, f);
          if (Array.isArray(G)) {
            var Q = dN(G, 2);
            X = Q[0], J = Q[1];
          } else
            X = G;
        }
        return (
          // eslint-disable-next-line react/no-array-index-key
          /* @__PURE__ */ M.createElement("li", {
            className: "recharts-tooltip-item",
            key: "tooltip-item-".concat(R),
            style: L
          }, Ve(J) ? /* @__PURE__ */ M.createElement("span", {
            className: "recharts-tooltip-item-name"
          }, J) : null, Ve(J) ? /* @__PURE__ */ M.createElement("span", {
            className: "recharts-tooltip-item-separator"
          }, n) : null, /* @__PURE__ */ M.createElement("span", {
            className: "recharts-tooltip-item-value"
          }, X), /* @__PURE__ */ M.createElement("span", {
            className: "recharts-tooltip-item-unit"
          }, D.unit || ""))
        );
      });
      return /* @__PURE__ */ M.createElement("ul", {
        className: "recharts-tooltip-item-list",
        style: N
      }, $);
    }
    return null;
  }, _ = nf({
    margin: 0,
    padding: 10,
    backgroundColor: "#fff",
    border: "1px solid #ccc",
    whiteSpace: "nowrap"
  }, i), m = nf({
    margin: 0
  }, s), O = !me(v), x = O ? v : "", S = _e("recharts-default-tooltip", h), T = _e("recharts-tooltip-label", y);
  O && p && f !== void 0 && f !== null && (x = p(v, f));
  var C = b ? {
    role: "status",
    "aria-live": "assertive"
  } : {};
  return /* @__PURE__ */ M.createElement("div", _d({
    className: S,
    style: _
  }, C), /* @__PURE__ */ M.createElement("p", {
    className: T,
    style: m
  }, /* @__PURE__ */ M.isValidElement(x) ? x : "".concat(x)), w());
};
function Ka(e) {
  "@babel/helpers - typeof";
  return Ka = typeof Symbol == "function" && typeof Symbol.iterator == "symbol" ? function(t) {
    return typeof t;
  } : function(t) {
    return t && typeof Symbol == "function" && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t;
  }, Ka(e);
}
function Zi(e, t, r) {
  return t = ON(t), t in e ? Object.defineProperty(e, t, { value: r, enumerable: !0, configurable: !0, writable: !0 }) : e[t] = r, e;
}
function ON(e) {
  var t = _N(e, "string");
  return Ka(t) == "symbol" ? t : t + "";
}
function _N(e, t) {
  if (Ka(e) != "object" || !e) return e;
  var r = e[Symbol.toPrimitive];
  if (r !== void 0) {
    var n = r.call(e, t);
    if (Ka(n) != "object") return n;
    throw new TypeError("@@toPrimitive must return a primitive value.");
  }
  return (t === "string" ? String : Number)(e);
}
var ba = "recharts-tooltip-wrapper", SN = {
  visibility: "hidden"
};
function PN(e) {
  var t = e.coordinate, r = e.translateX, n = e.translateY;
  return _e(ba, Zi(Zi(Zi(Zi({}, "".concat(ba, "-right"), H(r) && t && H(t.x) && r >= t.x), "".concat(ba, "-left"), H(r) && t && H(t.x) && r < t.x), "".concat(ba, "-bottom"), H(n) && t && H(t.y) && n >= t.y), "".concat(ba, "-top"), H(n) && t && H(t.y) && n < t.y));
}
function Zg(e) {
  var t = e.allowEscapeViewBox, r = e.coordinate, n = e.key, a = e.offsetTopLeft, i = e.position, o = e.reverseDirection, u = e.tooltipDimension, l = e.viewBox, s = e.viewBoxDimension;
  if (i && H(i[n]))
    return i[n];
  var f = r[n] - u - a, c = r[n] + a;
  if (t[n])
    return o[n] ? f : c;
  if (o[n]) {
    var d = f, h = l[n];
    return d < h ? Math.max(c, l[n]) : Math.max(f, l[n]);
  }
  var y = c + u, v = l[n] + s;
  return y > v ? Math.max(f, l[n]) : Math.max(c, l[n]);
}
function AN(e) {
  var t = e.translateX, r = e.translateY, n = e.useTranslate3d;
  return {
    transform: n ? "translate3d(".concat(t, "px, ").concat(r, "px, 0)") : "translate(".concat(t, "px, ").concat(r, "px)")
  };
}
function EN(e) {
  var t = e.allowEscapeViewBox, r = e.coordinate, n = e.offsetTopLeft, a = e.position, i = e.reverseDirection, o = e.tooltipBox, u = e.useTranslate3d, l = e.viewBox, s, f, c;
  return o.height > 0 && o.width > 0 && r ? (f = Zg({
    allowEscapeViewBox: t,
    coordinate: r,
    key: "x",
    offsetTopLeft: n,
    position: a,
    reverseDirection: i,
    tooltipDimension: o.width,
    viewBox: l,
    viewBoxDimension: l.width
  }), c = Zg({
    allowEscapeViewBox: t,
    coordinate: r,
    key: "y",
    offsetTopLeft: n,
    position: a,
    reverseDirection: i,
    tooltipDimension: o.height,
    viewBox: l,
    viewBoxDimension: l.height
  }), s = AN({
    translateX: f,
    translateY: c,
    useTranslate3d: u
  })) : s = SN, {
    cssProperties: s,
    cssClasses: PN({
      translateX: f,
      translateY: c,
      coordinate: r
    })
  };
}
function Ln(e) {
  "@babel/helpers - typeof";
  return Ln = typeof Symbol == "function" && typeof Symbol.iterator == "symbol" ? function(t) {
    return typeof t;
  } : function(t) {
    return t && typeof Symbol == "function" && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t;
  }, Ln(e);
}
function Jg(e, t) {
  var r = Object.keys(e);
  if (Object.getOwnPropertySymbols) {
    var n = Object.getOwnPropertySymbols(e);
    t && (n = n.filter(function(a) {
      return Object.getOwnPropertyDescriptor(e, a).enumerable;
    })), r.push.apply(r, n);
  }
  return r;
}
function Qg(e) {
  for (var t = 1; t < arguments.length; t++) {
    var r = arguments[t] != null ? arguments[t] : {};
    t % 2 ? Jg(Object(r), !0).forEach(function(n) {
      Pd(e, n, r[n]);
    }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(r)) : Jg(Object(r)).forEach(function(n) {
      Object.defineProperty(e, n, Object.getOwnPropertyDescriptor(r, n));
    });
  }
  return e;
}
function TN(e, t) {
  if (!(e instanceof t))
    throw new TypeError("Cannot call a class as a function");
}
function MN(e, t) {
  for (var r = 0; r < t.length; r++) {
    var n = t[r];
    n.enumerable = n.enumerable || !1, n.configurable = !0, "value" in n && (n.writable = !0), Object.defineProperty(e, nO(n.key), n);
  }
}
function jN(e, t, r) {
  return t && MN(e.prototype, t), Object.defineProperty(e, "prototype", { writable: !1 }), e;
}
function NN(e, t, r) {
  return t = Ao(t), CN(e, rO() ? Reflect.construct(t, r || [], Ao(e).constructor) : t.apply(e, r));
}
function CN(e, t) {
  if (t && (Ln(t) === "object" || typeof t == "function"))
    return t;
  if (t !== void 0)
    throw new TypeError("Derived constructors may only return object or undefined");
  return $N(e);
}
function $N(e) {
  if (e === void 0)
    throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
  return e;
}
function rO() {
  try {
    var e = !Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function() {
    }));
  } catch {
  }
  return (rO = function() {
    return !!e;
  })();
}
function Ao(e) {
  return Ao = Object.setPrototypeOf ? Object.getPrototypeOf.bind() : function(r) {
    return r.__proto__ || Object.getPrototypeOf(r);
  }, Ao(e);
}
function RN(e, t) {
  if (typeof t != "function" && t !== null)
    throw new TypeError("Super expression must either be null or a function");
  e.prototype = Object.create(t && t.prototype, { constructor: { value: e, writable: !0, configurable: !0 } }), Object.defineProperty(e, "prototype", { writable: !1 }), t && Sd(e, t);
}
function Sd(e, t) {
  return Sd = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function(n, a) {
    return n.__proto__ = a, n;
  }, Sd(e, t);
}
function Pd(e, t, r) {
  return t = nO(t), t in e ? Object.defineProperty(e, t, { value: r, enumerable: !0, configurable: !0, writable: !0 }) : e[t] = r, e;
}
function nO(e) {
  var t = kN(e, "string");
  return Ln(t) == "symbol" ? t : t + "";
}
function kN(e, t) {
  if (Ln(e) != "object" || !e) return e;
  var r = e[Symbol.toPrimitive];
  if (r !== void 0) {
    var n = r.call(e, t);
    if (Ln(n) != "object") return n;
    throw new TypeError("@@toPrimitive must return a primitive value.");
  }
  return String(e);
}
var e0 = 1, IN = /* @__PURE__ */ (function(e) {
  function t() {
    var r;
    TN(this, t);
    for (var n = arguments.length, a = new Array(n), i = 0; i < n; i++)
      a[i] = arguments[i];
    return r = NN(this, t, [].concat(a)), Pd(r, "state", {
      dismissed: !1,
      dismissedAtCoordinate: {
        x: 0,
        y: 0
      },
      lastBoundingBox: {
        width: -1,
        height: -1
      }
    }), Pd(r, "handleKeyDown", function(o) {
      if (o.key === "Escape") {
        var u, l, s, f;
        r.setState({
          dismissed: !0,
          dismissedAtCoordinate: {
            x: (u = (l = r.props.coordinate) === null || l === void 0 ? void 0 : l.x) !== null && u !== void 0 ? u : 0,
            y: (s = (f = r.props.coordinate) === null || f === void 0 ? void 0 : f.y) !== null && s !== void 0 ? s : 0
          }
        });
      }
    }), r;
  }
  return RN(t, e), jN(t, [{
    key: "updateBBox",
    value: function() {
      if (this.wrapperNode && this.wrapperNode.getBoundingClientRect) {
        var n = this.wrapperNode.getBoundingClientRect();
        (Math.abs(n.width - this.state.lastBoundingBox.width) > e0 || Math.abs(n.height - this.state.lastBoundingBox.height) > e0) && this.setState({
          lastBoundingBox: {
            width: n.width,
            height: n.height
          }
        });
      } else (this.state.lastBoundingBox.width !== -1 || this.state.lastBoundingBox.height !== -1) && this.setState({
        lastBoundingBox: {
          width: -1,
          height: -1
        }
      });
    }
  }, {
    key: "componentDidMount",
    value: function() {
      document.addEventListener("keydown", this.handleKeyDown), this.updateBBox();
    }
  }, {
    key: "componentWillUnmount",
    value: function() {
      document.removeEventListener("keydown", this.handleKeyDown);
    }
  }, {
    key: "componentDidUpdate",
    value: function() {
      var n, a;
      this.props.active && this.updateBBox(), this.state.dismissed && (((n = this.props.coordinate) === null || n === void 0 ? void 0 : n.x) !== this.state.dismissedAtCoordinate.x || ((a = this.props.coordinate) === null || a === void 0 ? void 0 : a.y) !== this.state.dismissedAtCoordinate.y) && (this.state.dismissed = !1);
    }
  }, {
    key: "render",
    value: function() {
      var n = this, a = this.props, i = a.active, o = a.allowEscapeViewBox, u = a.animationDuration, l = a.animationEasing, s = a.children, f = a.coordinate, c = a.hasPayload, d = a.isAnimationActive, h = a.offset, y = a.position, v = a.reverseDirection, p = a.useTranslate3d, g = a.viewBox, b = a.wrapperStyle, w = EN({
        allowEscapeViewBox: o,
        coordinate: f,
        offsetTopLeft: h,
        position: y,
        reverseDirection: v,
        tooltipBox: this.state.lastBoundingBox,
        useTranslate3d: p,
        viewBox: g
      }), _ = w.cssClasses, m = w.cssProperties, O = Qg(Qg({
        transition: d && i ? "transform ".concat(u, "ms ").concat(l) : void 0
      }, m), {}, {
        pointerEvents: "none",
        visibility: !this.state.dismissed && i && c ? "visible" : "hidden",
        position: "absolute",
        top: 0,
        left: 0
      }, b);
      return (
        // This element allow listening to the `Escape` key.
        // See https://github.com/recharts/recharts/pull/2925
        /* @__PURE__ */ M.createElement("div", {
          tabIndex: -1,
          className: _,
          style: O,
          ref: function(S) {
            n.wrapperNode = S;
          }
        }, s)
      );
    }
  }]);
})(br), DN = function() {
  return !(typeof window < "u" && window.document && window.document.createElement && window.setTimeout);
}, ua = {
  isSsr: DN()
};
function qn(e) {
  "@babel/helpers - typeof";
  return qn = typeof Symbol == "function" && typeof Symbol.iterator == "symbol" ? function(t) {
    return typeof t;
  } : function(t) {
    return t && typeof Symbol == "function" && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t;
  }, qn(e);
}
function t0(e, t) {
  var r = Object.keys(e);
  if (Object.getOwnPropertySymbols) {
    var n = Object.getOwnPropertySymbols(e);
    t && (n = n.filter(function(a) {
      return Object.getOwnPropertyDescriptor(e, a).enumerable;
    })), r.push.apply(r, n);
  }
  return r;
}
function r0(e) {
  for (var t = 1; t < arguments.length; t++) {
    var r = arguments[t] != null ? arguments[t] : {};
    t % 2 ? t0(Object(r), !0).forEach(function(n) {
      sp(e, n, r[n]);
    }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(r)) : t0(Object(r)).forEach(function(n) {
      Object.defineProperty(e, n, Object.getOwnPropertyDescriptor(r, n));
    });
  }
  return e;
}
function LN(e, t) {
  if (!(e instanceof t))
    throw new TypeError("Cannot call a class as a function");
}
function qN(e, t) {
  for (var r = 0; r < t.length; r++) {
    var n = t[r];
    n.enumerable = n.enumerable || !1, n.configurable = !0, "value" in n && (n.writable = !0), Object.defineProperty(e, iO(n.key), n);
  }
}
function BN(e, t, r) {
  return t && qN(e.prototype, t), Object.defineProperty(e, "prototype", { writable: !1 }), e;
}
function FN(e, t, r) {
  return t = Eo(t), zN(e, aO() ? Reflect.construct(t, r || [], Eo(e).constructor) : t.apply(e, r));
}
function zN(e, t) {
  if (t && (qn(t) === "object" || typeof t == "function"))
    return t;
  if (t !== void 0)
    throw new TypeError("Derived constructors may only return object or undefined");
  return UN(e);
}
function UN(e) {
  if (e === void 0)
    throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
  return e;
}
function aO() {
  try {
    var e = !Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function() {
    }));
  } catch {
  }
  return (aO = function() {
    return !!e;
  })();
}
function Eo(e) {
  return Eo = Object.setPrototypeOf ? Object.getPrototypeOf.bind() : function(r) {
    return r.__proto__ || Object.getPrototypeOf(r);
  }, Eo(e);
}
function WN(e, t) {
  if (typeof t != "function" && t !== null)
    throw new TypeError("Super expression must either be null or a function");
  e.prototype = Object.create(t && t.prototype, { constructor: { value: e, writable: !0, configurable: !0 } }), Object.defineProperty(e, "prototype", { writable: !1 }), t && Ad(e, t);
}
function Ad(e, t) {
  return Ad = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function(n, a) {
    return n.__proto__ = a, n;
  }, Ad(e, t);
}
function sp(e, t, r) {
  return t = iO(t), t in e ? Object.defineProperty(e, t, { value: r, enumerable: !0, configurable: !0, writable: !0 }) : e[t] = r, e;
}
function iO(e) {
  var t = HN(e, "string");
  return qn(t) == "symbol" ? t : t + "";
}
function HN(e, t) {
  if (qn(e) != "object" || !e) return e;
  var r = e[Symbol.toPrimitive];
  if (r !== void 0) {
    var n = r.call(e, t);
    if (qn(n) != "object") return n;
    throw new TypeError("@@toPrimitive must return a primitive value.");
  }
  return String(e);
}
function GN(e) {
  return e.dataKey;
}
function KN(e, t) {
  return /* @__PURE__ */ M.isValidElement(e) ? /* @__PURE__ */ M.cloneElement(e, t) : typeof e == "function" ? /* @__PURE__ */ M.createElement(e, t) : /* @__PURE__ */ M.createElement(wN, t);
}
var Pt = /* @__PURE__ */ (function(e) {
  function t() {
    return LN(this, t), FN(this, t, arguments);
  }
  return WN(t, e), BN(t, [{
    key: "render",
    value: function() {
      var n = this, a = this.props, i = a.active, o = a.allowEscapeViewBox, u = a.animationDuration, l = a.animationEasing, s = a.content, f = a.coordinate, c = a.filterNull, d = a.isAnimationActive, h = a.offset, y = a.payload, v = a.payloadUniqBy, p = a.position, g = a.reverseDirection, b = a.useTranslate3d, w = a.viewBox, _ = a.wrapperStyle, m = y ?? [];
      c && m.length && (m = Xw(y.filter(function(x) {
        return x.value != null && (x.hide !== !0 || n.props.includeHidden);
      }), v, GN));
      var O = m.length > 0;
      return /* @__PURE__ */ M.createElement(IN, {
        allowEscapeViewBox: o,
        animationDuration: u,
        animationEasing: l,
        isAnimationActive: d,
        active: i,
        coordinate: f,
        hasPayload: O,
        offset: h,
        position: p,
        reverseDirection: g,
        useTranslate3d: b,
        viewBox: w,
        wrapperStyle: _
      }, KN(s, r0(r0({}, this.props), {}, {
        payload: m
      })));
    }
  }]);
})(br);
sp(Pt, "displayName", "Tooltip");
sp(Pt, "defaultProps", {
  accessibilityLayer: !1,
  allowEscapeViewBox: {
    x: !1,
    y: !1
  },
  animationDuration: 400,
  animationEasing: "ease",
  contentStyle: {},
  coordinate: {
    x: 0,
    y: 0
  },
  cursor: !0,
  cursorStyle: {},
  filterNull: !0,
  isAnimationActive: !ua.isSsr,
  itemStyle: {},
  labelStyle: {},
  offset: 10,
  reverseDirection: {
    x: !1,
    y: !1
  },
  separator: " : ",
  trigger: "hover",
  useTranslate3d: !1,
  viewBox: {
    x: 0,
    y: 0,
    height: 0,
    width: 0
  },
  wrapperStyle: {}
});
var af, n0;
function VN() {
  if (n0) return af;
  n0 = 1;
  var e = Qt(), t = function() {
    return e.Date.now();
  };
  return af = t, af;
}
var of, a0;
function XN() {
  if (a0) return of;
  a0 = 1;
  var e = /\s/;
  function t(r) {
    for (var n = r.length; n-- && e.test(r.charAt(n)); )
      ;
    return n;
  }
  return of = t, of;
}
var uf, i0;
function YN() {
  if (i0) return uf;
  i0 = 1;
  var e = XN(), t = /^\s+/;
  function r(n) {
    return n && n.slice(0, e(n) + 1).replace(t, "");
  }
  return uf = r, uf;
}
var lf, o0;
function oO() {
  if (o0) return lf;
  o0 = 1;
  var e = YN(), t = Lr(), r = na(), n = NaN, a = /^[-+]0x[0-9a-f]+$/i, i = /^0b[01]+$/i, o = /^0o[0-7]+$/i, u = parseInt;
  function l(s) {
    if (typeof s == "number")
      return s;
    if (r(s))
      return n;
    if (t(s)) {
      var f = typeof s.valueOf == "function" ? s.valueOf() : s;
      s = t(f) ? f + "" : f;
    }
    if (typeof s != "string")
      return s === 0 ? s : +s;
    s = e(s);
    var c = i.test(s);
    return c || o.test(s) ? u(s.slice(2), c ? 2 : 8) : a.test(s) ? n : +s;
  }
  return lf = l, lf;
}
var sf, u0;
function ZN() {
  if (u0) return sf;
  u0 = 1;
  var e = Lr(), t = VN(), r = oO(), n = "Expected a function", a = Math.max, i = Math.min;
  function o(u, l, s) {
    var f, c, d, h, y, v, p = 0, g = !1, b = !1, w = !0;
    if (typeof u != "function")
      throw new TypeError(n);
    l = r(l) || 0, e(s) && (g = !!s.leading, b = "maxWait" in s, d = b ? a(r(s.maxWait) || 0, l) : d, w = "trailing" in s ? !!s.trailing : w);
    function _($) {
      var D = f, R = c;
      return f = c = void 0, p = $, h = u.apply(R, D), h;
    }
    function m($) {
      return p = $, y = setTimeout(S, l), g ? _($) : h;
    }
    function O($) {
      var D = $ - v, R = $ - p, L = l - D;
      return b ? i(L, d - R) : L;
    }
    function x($) {
      var D = $ - v, R = $ - p;
      return v === void 0 || D >= l || D < 0 || b && R >= d;
    }
    function S() {
      var $ = t();
      if (x($))
        return T($);
      y = setTimeout(S, O($));
    }
    function T($) {
      return y = void 0, w && f ? _($) : (f = c = void 0, h);
    }
    function C() {
      y !== void 0 && clearTimeout(y), p = 0, f = v = c = y = void 0;
    }
    function A() {
      return y === void 0 ? h : T(t());
    }
    function N() {
      var $ = t(), D = x($);
      if (f = arguments, c = this, v = $, D) {
        if (y === void 0)
          return m(v);
        if (b)
          return clearTimeout(y), y = setTimeout(S, l), _(v);
      }
      return y === void 0 && (y = setTimeout(S, l)), h;
    }
    return N.cancel = C, N.flush = A, N;
  }
  return sf = o, sf;
}
var cf, l0;
function JN() {
  if (l0) return cf;
  l0 = 1;
  var e = ZN(), t = Lr(), r = "Expected a function";
  function n(a, i, o) {
    var u = !0, l = !0;
    if (typeof a != "function")
      throw new TypeError(r);
    return t(o) && (u = "leading" in o ? !!o.leading : u, l = "trailing" in o ? !!o.trailing : l), e(a, i, {
      leading: u,
      maxWait: i,
      trailing: l
    });
  }
  return cf = n, cf;
}
var QN = JN();
const uO = /* @__PURE__ */ $e(QN);
function Va(e) {
  "@babel/helpers - typeof";
  return Va = typeof Symbol == "function" && typeof Symbol.iterator == "symbol" ? function(t) {
    return typeof t;
  } : function(t) {
    return t && typeof Symbol == "function" && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t;
  }, Va(e);
}
function s0(e, t) {
  var r = Object.keys(e);
  if (Object.getOwnPropertySymbols) {
    var n = Object.getOwnPropertySymbols(e);
    t && (n = n.filter(function(a) {
      return Object.getOwnPropertyDescriptor(e, a).enumerable;
    })), r.push.apply(r, n);
  }
  return r;
}
function Ji(e) {
  for (var t = 1; t < arguments.length; t++) {
    var r = arguments[t] != null ? arguments[t] : {};
    t % 2 ? s0(Object(r), !0).forEach(function(n) {
      eC(e, n, r[n]);
    }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(r)) : s0(Object(r)).forEach(function(n) {
      Object.defineProperty(e, n, Object.getOwnPropertyDescriptor(r, n));
    });
  }
  return e;
}
function eC(e, t, r) {
  return t = tC(t), t in e ? Object.defineProperty(e, t, { value: r, enumerable: !0, configurable: !0, writable: !0 }) : e[t] = r, e;
}
function tC(e) {
  var t = rC(e, "string");
  return Va(t) == "symbol" ? t : t + "";
}
function rC(e, t) {
  if (Va(e) != "object" || !e) return e;
  var r = e[Symbol.toPrimitive];
  if (r !== void 0) {
    var n = r.call(e, t);
    if (Va(n) != "object") return n;
    throw new TypeError("@@toPrimitive must return a primitive value.");
  }
  return (t === "string" ? String : Number)(e);
}
function nC(e, t) {
  return uC(e) || oC(e, t) || iC(e, t) || aC();
}
function aC() {
  throw new TypeError(`Invalid attempt to destructure non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`);
}
function iC(e, t) {
  if (e) {
    if (typeof e == "string") return c0(e, t);
    var r = Object.prototype.toString.call(e).slice(8, -1);
    if (r === "Object" && e.constructor && (r = e.constructor.name), r === "Map" || r === "Set") return Array.from(e);
    if (r === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r)) return c0(e, t);
  }
}
function c0(e, t) {
  (t == null || t > e.length) && (t = e.length);
  for (var r = 0, n = new Array(t); r < t; r++) n[r] = e[r];
  return n;
}
function oC(e, t) {
  var r = e == null ? null : typeof Symbol < "u" && e[Symbol.iterator] || e["@@iterator"];
  if (r != null) {
    var n, a, i, o, u = [], l = !0, s = !1;
    try {
      if (i = (r = r.call(e)).next, t !== 0) for (; !(l = (n = i.call(r)).done) && (u.push(n.value), u.length !== t); l = !0) ;
    } catch (f) {
      s = !0, a = f;
    } finally {
      try {
        if (!l && r.return != null && (o = r.return(), Object(o) !== o)) return;
      } finally {
        if (s) throw a;
      }
    }
    return u;
  }
}
function uC(e) {
  if (Array.isArray(e)) return e;
}
var Ed = /* @__PURE__ */ Ir(function(e, t) {
  var r = e.aspect, n = e.initialDimension, a = n === void 0 ? {
    width: -1,
    height: -1
  } : n, i = e.width, o = i === void 0 ? "100%" : i, u = e.height, l = u === void 0 ? "100%" : u, s = e.minWidth, f = s === void 0 ? 0 : s, c = e.minHeight, d = e.maxHeight, h = e.children, y = e.debounce, v = y === void 0 ? 0 : y, p = e.id, g = e.className, b = e.onResize, w = e.style, _ = w === void 0 ? {} : w, m = pr(null), O = pr();
  O.current = b, _1(t, function() {
    return Object.defineProperty(m.current, "current", {
      get: function() {
        return console.warn("The usage of ref.current.current is deprecated and will no longer be supported."), m.current;
      },
      configurable: !0
    });
  });
  var x = Oe({
    containerWidth: a.width,
    containerHeight: a.height
  }), S = nC(x, 2), T = S[0], C = S[1], A = dn(function($, D) {
    C(function(R) {
      var L = Math.round($), z = Math.round(D);
      return R.containerWidth === L && R.containerHeight === z ? R : {
        containerWidth: L,
        containerHeight: z
      };
    });
  }, []);
  It(function() {
    var $ = function(W) {
      var X, J = W[0].contentRect, G = J.width, Q = J.height;
      A(G, Q), (X = O.current) === null || X === void 0 || X.call(O, G, Q);
    };
    v > 0 && ($ = uO($, v, {
      trailing: !0,
      leading: !1
    }));
    var D = new ResizeObserver($), R = m.current.getBoundingClientRect(), L = R.width, z = R.height;
    return A(L, z), D.observe(m.current), function() {
      D.disconnect();
    };
  }, [A, v]);
  var N = ft(function() {
    var $ = T.containerWidth, D = T.containerHeight;
    if ($ < 0 || D < 0)
      return null;
    dr(Zr(o) || Zr(l), `The width(%s) and height(%s) are both fixed numbers,
       maybe you don't need to use a ResponsiveContainer.`, o, l), dr(!r || r > 0, "The aspect(%s) must be greater than zero.", r);
    var R = Zr(o) ? $ : o, L = Zr(l) ? D : l;
    r && r > 0 && (R ? L = R / r : L && (R = L * r), d && L > d && (L = d)), dr(R > 0 || L > 0, `The width(%s) and height(%s) of chart should be greater than 0,
       please check the style of container, or the props width(%s) and height(%s),
       or add a minWidth(%s) or minHeight(%s) or use aspect(%s) to control the
       height and width.`, R, L, o, l, f, c, r);
    var z = !Array.isArray(h) && fr(h.type).endsWith("Chart");
    return M.Children.map(h, function(F) {
      return /* @__PURE__ */ M.isValidElement(F) ? /* @__PURE__ */ Ue(F, Ji({
        width: R,
        height: L
      }, z ? {
        style: Ji({
          height: "100%",
          width: "100%",
          maxHeight: L,
          maxWidth: R
        }, F.props.style)
      } : {})) : F;
    });
  }, [r, h, l, d, c, f, T, o]);
  return /* @__PURE__ */ M.createElement("div", {
    id: p ? "".concat(p) : void 0,
    className: _e("recharts-responsive-container", g),
    style: Ji(Ji({}, _), {}, {
      width: o,
      height: l,
      minWidth: f,
      minHeight: c,
      maxHeight: d
    }),
    ref: m
  }, N);
}), lO = function(t) {
  return null;
};
lO.displayName = "Cell";
function Xa(e) {
  "@babel/helpers - typeof";
  return Xa = typeof Symbol == "function" && typeof Symbol.iterator == "symbol" ? function(t) {
    return typeof t;
  } : function(t) {
    return t && typeof Symbol == "function" && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t;
  }, Xa(e);
}
function f0(e, t) {
  var r = Object.keys(e);
  if (Object.getOwnPropertySymbols) {
    var n = Object.getOwnPropertySymbols(e);
    t && (n = n.filter(function(a) {
      return Object.getOwnPropertyDescriptor(e, a).enumerable;
    })), r.push.apply(r, n);
  }
  return r;
}
function Td(e) {
  for (var t = 1; t < arguments.length; t++) {
    var r = arguments[t] != null ? arguments[t] : {};
    t % 2 ? f0(Object(r), !0).forEach(function(n) {
      lC(e, n, r[n]);
    }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(r)) : f0(Object(r)).forEach(function(n) {
      Object.defineProperty(e, n, Object.getOwnPropertyDescriptor(r, n));
    });
  }
  return e;
}
function lC(e, t, r) {
  return t = sC(t), t in e ? Object.defineProperty(e, t, { value: r, enumerable: !0, configurable: !0, writable: !0 }) : e[t] = r, e;
}
function sC(e) {
  var t = cC(e, "string");
  return Xa(t) == "symbol" ? t : t + "";
}
function cC(e, t) {
  if (Xa(e) != "object" || !e) return e;
  var r = e[Symbol.toPrimitive];
  if (r !== void 0) {
    var n = r.call(e, t);
    if (Xa(n) != "object") return n;
    throw new TypeError("@@toPrimitive must return a primitive value.");
  }
  return (t === "string" ? String : Number)(e);
}
var wn = {
  widthCache: {},
  cacheCount: 0
}, fC = 2e3, dC = {
  position: "absolute",
  top: "-20000px",
  left: 0,
  padding: 0,
  margin: 0,
  border: "none",
  whiteSpace: "pre"
}, d0 = "recharts_measurement_span";
function hC(e) {
  var t = Td({}, e);
  return Object.keys(t).forEach(function(r) {
    t[r] || delete t[r];
  }), t;
}
var ka = function(t) {
  var r = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : {};
  if (t == null || ua.isSsr)
    return {
      width: 0,
      height: 0
    };
  var n = hC(r), a = JSON.stringify({
    text: t,
    copyStyle: n
  });
  if (wn.widthCache[a])
    return wn.widthCache[a];
  try {
    var i = document.getElementById(d0);
    i || (i = document.createElement("span"), i.setAttribute("id", d0), i.setAttribute("aria-hidden", "true"), document.body.appendChild(i));
    var o = Td(Td({}, dC), n);
    Object.assign(i.style, o), i.textContent = "".concat(t);
    var u = i.getBoundingClientRect(), l = {
      width: u.width,
      height: u.height
    };
    return wn.widthCache[a] = l, ++wn.cacheCount > fC && (wn.cacheCount = 0, wn.widthCache = {}), l;
  } catch {
    return {
      width: 0,
      height: 0
    };
  }
}, pC = function(t) {
  return {
    top: t.top + window.scrollY - document.documentElement.clientTop,
    left: t.left + window.scrollX - document.documentElement.clientLeft
  };
};
function Ya(e) {
  "@babel/helpers - typeof";
  return Ya = typeof Symbol == "function" && typeof Symbol.iterator == "symbol" ? function(t) {
    return typeof t;
  } : function(t) {
    return t && typeof Symbol == "function" && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t;
  }, Ya(e);
}
function To(e, t) {
  return gC(e) || mC(e, t) || yC(e, t) || vC();
}
function vC() {
  throw new TypeError(`Invalid attempt to destructure non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`);
}
function yC(e, t) {
  if (e) {
    if (typeof e == "string") return h0(e, t);
    var r = Object.prototype.toString.call(e).slice(8, -1);
    if (r === "Object" && e.constructor && (r = e.constructor.name), r === "Map" || r === "Set") return Array.from(e);
    if (r === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r)) return h0(e, t);
  }
}
function h0(e, t) {
  (t == null || t > e.length) && (t = e.length);
  for (var r = 0, n = new Array(t); r < t; r++) n[r] = e[r];
  return n;
}
function mC(e, t) {
  var r = e == null ? null : typeof Symbol < "u" && e[Symbol.iterator] || e["@@iterator"];
  if (r != null) {
    var n, a, i, o, u = [], l = !0, s = !1;
    try {
      if (i = (r = r.call(e)).next, t === 0) {
        if (Object(r) !== r) return;
        l = !1;
      } else for (; !(l = (n = i.call(r)).done) && (u.push(n.value), u.length !== t); l = !0) ;
    } catch (f) {
      s = !0, a = f;
    } finally {
      try {
        if (!l && r.return != null && (o = r.return(), Object(o) !== o)) return;
      } finally {
        if (s) throw a;
      }
    }
    return u;
  }
}
function gC(e) {
  if (Array.isArray(e)) return e;
}
function bC(e, t) {
  if (!(e instanceof t))
    throw new TypeError("Cannot call a class as a function");
}
function p0(e, t) {
  for (var r = 0; r < t.length; r++) {
    var n = t[r];
    n.enumerable = n.enumerable || !1, n.configurable = !0, "value" in n && (n.writable = !0), Object.defineProperty(e, wC(n.key), n);
  }
}
function xC(e, t, r) {
  return t && p0(e.prototype, t), r && p0(e, r), Object.defineProperty(e, "prototype", { writable: !1 }), e;
}
function wC(e) {
  var t = OC(e, "string");
  return Ya(t) == "symbol" ? t : t + "";
}
function OC(e, t) {
  if (Ya(e) != "object" || !e) return e;
  var r = e[Symbol.toPrimitive];
  if (r !== void 0) {
    var n = r.call(e, t);
    if (Ya(n) != "object") return n;
    throw new TypeError("@@toPrimitive must return a primitive value.");
  }
  return String(e);
}
var v0 = /(-?\d+(?:\.\d+)?[a-zA-Z%]*)([*/])(-?\d+(?:\.\d+)?[a-zA-Z%]*)/, y0 = /(-?\d+(?:\.\d+)?[a-zA-Z%]*)([+-])(-?\d+(?:\.\d+)?[a-zA-Z%]*)/, _C = /^px|cm|vh|vw|em|rem|%|mm|in|pt|pc|ex|ch|vmin|vmax|Q$/, SC = /(-?\d+(?:\.\d+)?)([a-zA-Z%]+)?/, sO = {
  cm: 96 / 2.54,
  mm: 96 / 25.4,
  pt: 96 / 72,
  pc: 96 / 6,
  in: 96,
  Q: 96 / (2.54 * 40),
  px: 1
}, PC = Object.keys(sO), En = "NaN";
function AC(e, t) {
  return e * sO[t];
}
var Qi = /* @__PURE__ */ (function() {
  function e(t, r) {
    bC(this, e), this.num = t, this.unit = r, this.num = t, this.unit = r, Number.isNaN(t) && (this.unit = ""), r !== "" && !_C.test(r) && (this.num = NaN, this.unit = ""), PC.includes(r) && (this.num = AC(t, r), this.unit = "px");
  }
  return xC(e, [{
    key: "add",
    value: function(r) {
      return this.unit !== r.unit ? new e(NaN, "") : new e(this.num + r.num, this.unit);
    }
  }, {
    key: "subtract",
    value: function(r) {
      return this.unit !== r.unit ? new e(NaN, "") : new e(this.num - r.num, this.unit);
    }
  }, {
    key: "multiply",
    value: function(r) {
      return this.unit !== "" && r.unit !== "" && this.unit !== r.unit ? new e(NaN, "") : new e(this.num * r.num, this.unit || r.unit);
    }
  }, {
    key: "divide",
    value: function(r) {
      return this.unit !== "" && r.unit !== "" && this.unit !== r.unit ? new e(NaN, "") : new e(this.num / r.num, this.unit || r.unit);
    }
  }, {
    key: "toString",
    value: function() {
      return "".concat(this.num).concat(this.unit);
    }
  }, {
    key: "isNaN",
    value: function() {
      return Number.isNaN(this.num);
    }
  }], [{
    key: "parse",
    value: function(r) {
      var n, a = (n = SC.exec(r)) !== null && n !== void 0 ? n : [], i = To(a, 3), o = i[1], u = i[2];
      return new e(parseFloat(o), u ?? "");
    }
  }]);
})();
function cO(e) {
  if (e.includes(En))
    return En;
  for (var t = e; t.includes("*") || t.includes("/"); ) {
    var r, n = (r = v0.exec(t)) !== null && r !== void 0 ? r : [], a = To(n, 4), i = a[1], o = a[2], u = a[3], l = Qi.parse(i ?? ""), s = Qi.parse(u ?? ""), f = o === "*" ? l.multiply(s) : l.divide(s);
    if (f.isNaN())
      return En;
    t = t.replace(v0, f.toString());
  }
  for (; t.includes("+") || /.-\d+(?:\.\d+)?/.test(t); ) {
    var c, d = (c = y0.exec(t)) !== null && c !== void 0 ? c : [], h = To(d, 4), y = h[1], v = h[2], p = h[3], g = Qi.parse(y ?? ""), b = Qi.parse(p ?? ""), w = v === "+" ? g.add(b) : g.subtract(b);
    if (w.isNaN())
      return En;
    t = t.replace(y0, w.toString());
  }
  return t;
}
var m0 = /\(([^()]*)\)/;
function EC(e) {
  for (var t = e; t.includes("("); ) {
    var r = m0.exec(t), n = To(r, 2), a = n[1];
    t = t.replace(m0, cO(a));
  }
  return t;
}
function TC(e) {
  var t = e.replace(/\s+/g, "");
  return t = EC(t), t = cO(t), t;
}
function MC(e) {
  try {
    return TC(e);
  } catch {
    return En;
  }
}
function ff(e) {
  var t = MC(e.slice(5, -1));
  return t === En ? "" : t;
}
var jC = ["x", "y", "lineHeight", "capHeight", "scaleToFit", "textAnchor", "verticalAnchor", "fill"], NC = ["dx", "dy", "angle", "className", "breakAll"];
function Md() {
  return Md = Object.assign ? Object.assign.bind() : function(e) {
    for (var t = 1; t < arguments.length; t++) {
      var r = arguments[t];
      for (var n in r)
        Object.prototype.hasOwnProperty.call(r, n) && (e[n] = r[n]);
    }
    return e;
  }, Md.apply(this, arguments);
}
function g0(e, t) {
  if (e == null) return {};
  var r = CC(e, t), n, a;
  if (Object.getOwnPropertySymbols) {
    var i = Object.getOwnPropertySymbols(e);
    for (a = 0; a < i.length; a++)
      n = i[a], !(t.indexOf(n) >= 0) && Object.prototype.propertyIsEnumerable.call(e, n) && (r[n] = e[n]);
  }
  return r;
}
function CC(e, t) {
  if (e == null) return {};
  var r = {};
  for (var n in e)
    if (Object.prototype.hasOwnProperty.call(e, n)) {
      if (t.indexOf(n) >= 0) continue;
      r[n] = e[n];
    }
  return r;
}
function b0(e, t) {
  return IC(e) || kC(e, t) || RC(e, t) || $C();
}
function $C() {
  throw new TypeError(`Invalid attempt to destructure non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`);
}
function RC(e, t) {
  if (e) {
    if (typeof e == "string") return x0(e, t);
    var r = Object.prototype.toString.call(e).slice(8, -1);
    if (r === "Object" && e.constructor && (r = e.constructor.name), r === "Map" || r === "Set") return Array.from(e);
    if (r === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r)) return x0(e, t);
  }
}
function x0(e, t) {
  (t == null || t > e.length) && (t = e.length);
  for (var r = 0, n = new Array(t); r < t; r++) n[r] = e[r];
  return n;
}
function kC(e, t) {
  var r = e == null ? null : typeof Symbol < "u" && e[Symbol.iterator] || e["@@iterator"];
  if (r != null) {
    var n, a, i, o, u = [], l = !0, s = !1;
    try {
      if (i = (r = r.call(e)).next, t === 0) {
        if (Object(r) !== r) return;
        l = !1;
      } else for (; !(l = (n = i.call(r)).done) && (u.push(n.value), u.length !== t); l = !0) ;
    } catch (f) {
      s = !0, a = f;
    } finally {
      try {
        if (!l && r.return != null && (o = r.return(), Object(o) !== o)) return;
      } finally {
        if (s) throw a;
      }
    }
    return u;
  }
}
function IC(e) {
  if (Array.isArray(e)) return e;
}
var fO = /[ \f\n\r\t\v\u2028\u2029]+/, dO = function(t) {
  var r = t.children, n = t.breakAll, a = t.style;
  try {
    var i = [];
    me(r) || (n ? i = r.toString().split("") : i = r.toString().split(fO));
    var o = i.map(function(l) {
      return {
        word: l,
        width: ka(l, a).width
      };
    }), u = n ? 0 : ka(" ", a).width;
    return {
      wordsWithComputedWidth: o,
      spaceWidth: u
    };
  } catch {
    return null;
  }
}, DC = function(t, r, n, a, i) {
  var o = t.maxLines, u = t.children, l = t.style, s = t.breakAll, f = H(o), c = u, d = function() {
    var R = arguments.length > 0 && arguments[0] !== void 0 ? arguments[0] : [];
    return R.reduce(function(L, z) {
      var F = z.word, W = z.width, X = L[L.length - 1];
      if (X && (a == null || i || X.width + W + n < Number(a)))
        X.words.push(F), X.width += W + n;
      else {
        var J = {
          words: [F],
          width: W
        };
        L.push(J);
      }
      return L;
    }, []);
  }, h = d(r), y = function(R) {
    return R.reduce(function(L, z) {
      return L.width > z.width ? L : z;
    });
  };
  if (!f)
    return h;
  for (var v = "…", p = function(R) {
    var L = c.slice(0, R), z = dO({
      breakAll: s,
      style: l,
      children: L + v
    }).wordsWithComputedWidth, F = d(z), W = F.length > o || y(F).width > Number(a);
    return [W, F];
  }, g = 0, b = c.length - 1, w = 0, _; g <= b && w <= c.length - 1; ) {
    var m = Math.floor((g + b) / 2), O = m - 1, x = p(O), S = b0(x, 2), T = S[0], C = S[1], A = p(m), N = b0(A, 1), $ = N[0];
    if (!T && !$ && (g = m + 1), T && $ && (b = m - 1), !T && $) {
      _ = C;
      break;
    }
    w++;
  }
  return _ || h;
}, w0 = function(t) {
  var r = me(t) ? [] : t.toString().split(fO);
  return [{
    words: r
  }];
}, LC = function(t) {
  var r = t.width, n = t.scaleToFit, a = t.children, i = t.style, o = t.breakAll, u = t.maxLines;
  if ((r || n) && !ua.isSsr) {
    var l, s, f = dO({
      breakAll: o,
      children: a,
      style: i
    });
    if (f) {
      var c = f.wordsWithComputedWidth, d = f.spaceWidth;
      l = c, s = d;
    } else
      return w0(a);
    return DC({
      breakAll: o,
      children: a,
      maxLines: u,
      style: i
    }, l, s, r, n);
  }
  return w0(a);
}, O0 = "#808080", Mo = function(t) {
  var r = t.x, n = r === void 0 ? 0 : r, a = t.y, i = a === void 0 ? 0 : a, o = t.lineHeight, u = o === void 0 ? "1em" : o, l = t.capHeight, s = l === void 0 ? "0.71em" : l, f = t.scaleToFit, c = f === void 0 ? !1 : f, d = t.textAnchor, h = d === void 0 ? "start" : d, y = t.verticalAnchor, v = y === void 0 ? "end" : y, p = t.fill, g = p === void 0 ? O0 : p, b = g0(t, jC), w = ft(function() {
    return LC({
      breakAll: b.breakAll,
      children: b.children,
      maxLines: b.maxLines,
      scaleToFit: c,
      style: b.style,
      width: b.width
    });
  }, [b.breakAll, b.children, b.maxLines, c, b.style, b.width]), _ = b.dx, m = b.dy, O = b.angle, x = b.className, S = b.breakAll, T = g0(b, NC);
  if (!Ve(n) || !Ve(i))
    return null;
  var C = n + (H(_) ? _ : 0), A = i + (H(m) ? m : 0), N;
  switch (v) {
    case "start":
      N = ff("calc(".concat(s, ")"));
      break;
    case "middle":
      N = ff("calc(".concat((w.length - 1) / 2, " * -").concat(u, " + (").concat(s, " / 2))"));
      break;
    default:
      N = ff("calc(".concat(w.length - 1, " * -").concat(u, ")"));
      break;
  }
  var $ = [];
  if (c) {
    var D = w[0].width, R = b.width;
    $.push("scale(".concat((H(R) ? R / D : 1) / D, ")"));
  }
  return O && $.push("rotate(".concat(O, ", ").concat(C, ", ").concat(A, ")")), $.length && (T.transform = $.join(" ")), /* @__PURE__ */ M.createElement("text", Md({}, pe(T, !0), {
    x: C,
    y: A,
    className: _e("recharts-text", x),
    textAnchor: h,
    fill: g.includes("url") ? O0 : g
  }), w.map(function(L, z) {
    var F = L.words.join(S ? "" : " ");
    return (
      // duplicate words will cause duplicate keys
      // eslint-disable-next-line react/no-array-index-key
      /* @__PURE__ */ M.createElement("tspan", {
        x: C,
        dy: z === 0 ? N : u,
        key: "".concat(F, "-").concat(z)
      }, F)
    );
  }));
};
function Rr(e, t) {
  return e == null || t == null ? NaN : e < t ? -1 : e > t ? 1 : e >= t ? 0 : NaN;
}
function qC(e, t) {
  return e == null || t == null ? NaN : t < e ? -1 : t > e ? 1 : t >= e ? 0 : NaN;
}
function cp(e) {
  let t, r, n;
  e.length !== 2 ? (t = Rr, r = (u, l) => Rr(e(u), l), n = (u, l) => e(u) - l) : (t = e === Rr || e === qC ? e : BC, r = e, n = e);
  function a(u, l, s = 0, f = u.length) {
    if (s < f) {
      if (t(l, l) !== 0) return f;
      do {
        const c = s + f >>> 1;
        r(u[c], l) < 0 ? s = c + 1 : f = c;
      } while (s < f);
    }
    return s;
  }
  function i(u, l, s = 0, f = u.length) {
    if (s < f) {
      if (t(l, l) !== 0) return f;
      do {
        const c = s + f >>> 1;
        r(u[c], l) <= 0 ? s = c + 1 : f = c;
      } while (s < f);
    }
    return s;
  }
  function o(u, l, s = 0, f = u.length) {
    const c = a(u, l, s, f - 1);
    return c > s && n(u[c - 1], l) > -n(u[c], l) ? c - 1 : c;
  }
  return { left: a, center: o, right: i };
}
function BC() {
  return 0;
}
function hO(e) {
  return e === null ? NaN : +e;
}
function* FC(e, t) {
  for (let r of e)
    r != null && (r = +r) >= r && (yield r);
}
const zC = cp(Rr), ki = zC.right;
cp(hO).center;
class _0 extends Map {
  constructor(t, r = HC) {
    if (super(), Object.defineProperties(this, { _intern: { value: /* @__PURE__ */ new Map() }, _key: { value: r } }), t != null) for (const [n, a] of t) this.set(n, a);
  }
  get(t) {
    return super.get(S0(this, t));
  }
  has(t) {
    return super.has(S0(this, t));
  }
  set(t, r) {
    return super.set(UC(this, t), r);
  }
  delete(t) {
    return super.delete(WC(this, t));
  }
}
function S0({ _intern: e, _key: t }, r) {
  const n = t(r);
  return e.has(n) ? e.get(n) : r;
}
function UC({ _intern: e, _key: t }, r) {
  const n = t(r);
  return e.has(n) ? e.get(n) : (e.set(n, r), r);
}
function WC({ _intern: e, _key: t }, r) {
  const n = t(r);
  return e.has(n) && (r = e.get(n), e.delete(n)), r;
}
function HC(e) {
  return e !== null && typeof e == "object" ? e.valueOf() : e;
}
function GC(e = Rr) {
  if (e === Rr) return pO;
  if (typeof e != "function") throw new TypeError("compare is not a function");
  return (t, r) => {
    const n = e(t, r);
    return n || n === 0 ? n : (e(r, r) === 0) - (e(t, t) === 0);
  };
}
function pO(e, t) {
  return (e == null || !(e >= e)) - (t == null || !(t >= t)) || (e < t ? -1 : e > t ? 1 : 0);
}
const KC = Math.sqrt(50), VC = Math.sqrt(10), XC = Math.sqrt(2);
function jo(e, t, r) {
  const n = (t - e) / Math.max(0, r), a = Math.floor(Math.log10(n)), i = n / Math.pow(10, a), o = i >= KC ? 10 : i >= VC ? 5 : i >= XC ? 2 : 1;
  let u, l, s;
  return a < 0 ? (s = Math.pow(10, -a) / o, u = Math.round(e * s), l = Math.round(t * s), u / s < e && ++u, l / s > t && --l, s = -s) : (s = Math.pow(10, a) * o, u = Math.round(e / s), l = Math.round(t / s), u * s < e && ++u, l * s > t && --l), l < u && 0.5 <= r && r < 2 ? jo(e, t, r * 2) : [u, l, s];
}
function jd(e, t, r) {
  if (t = +t, e = +e, r = +r, !(r > 0)) return [];
  if (e === t) return [e];
  const n = t < e, [a, i, o] = n ? jo(t, e, r) : jo(e, t, r);
  if (!(i >= a)) return [];
  const u = i - a + 1, l = new Array(u);
  if (n)
    if (o < 0) for (let s = 0; s < u; ++s) l[s] = (i - s) / -o;
    else for (let s = 0; s < u; ++s) l[s] = (i - s) * o;
  else if (o < 0) for (let s = 0; s < u; ++s) l[s] = (a + s) / -o;
  else for (let s = 0; s < u; ++s) l[s] = (a + s) * o;
  return l;
}
function Nd(e, t, r) {
  return t = +t, e = +e, r = +r, jo(e, t, r)[2];
}
function Cd(e, t, r) {
  t = +t, e = +e, r = +r;
  const n = t < e, a = n ? Nd(t, e, r) : Nd(e, t, r);
  return (n ? -1 : 1) * (a < 0 ? 1 / -a : a);
}
function P0(e, t) {
  let r;
  for (const n of e)
    n != null && (r < n || r === void 0 && n >= n) && (r = n);
  return r;
}
function A0(e, t) {
  let r;
  for (const n of e)
    n != null && (r > n || r === void 0 && n >= n) && (r = n);
  return r;
}
function vO(e, t, r = 0, n = 1 / 0, a) {
  if (t = Math.floor(t), r = Math.floor(Math.max(0, r)), n = Math.floor(Math.min(e.length - 1, n)), !(r <= t && t <= n)) return e;
  for (a = a === void 0 ? pO : GC(a); n > r; ) {
    if (n - r > 600) {
      const l = n - r + 1, s = t - r + 1, f = Math.log(l), c = 0.5 * Math.exp(2 * f / 3), d = 0.5 * Math.sqrt(f * c * (l - c) / l) * (s - l / 2 < 0 ? -1 : 1), h = Math.max(r, Math.floor(t - s * c / l + d)), y = Math.min(n, Math.floor(t + (l - s) * c / l + d));
      vO(e, t, h, y, a);
    }
    const i = e[t];
    let o = r, u = n;
    for (xa(e, r, t), a(e[n], i) > 0 && xa(e, r, n); o < u; ) {
      for (xa(e, o, u), ++o, --u; a(e[o], i) < 0; ) ++o;
      for (; a(e[u], i) > 0; ) --u;
    }
    a(e[r], i) === 0 ? xa(e, r, u) : (++u, xa(e, u, n)), u <= t && (r = u + 1), t <= u && (n = u - 1);
  }
  return e;
}
function xa(e, t, r) {
  const n = e[t];
  e[t] = e[r], e[r] = n;
}
function YC(e, t, r) {
  if (e = Float64Array.from(FC(e)), !(!(n = e.length) || isNaN(t = +t))) {
    if (t <= 0 || n < 2) return A0(e);
    if (t >= 1) return P0(e);
    var n, a = (n - 1) * t, i = Math.floor(a), o = P0(vO(e, i).subarray(0, i + 1)), u = A0(e.subarray(i + 1));
    return o + (u - o) * (a - i);
  }
}
function ZC(e, t, r = hO) {
  if (!(!(n = e.length) || isNaN(t = +t))) {
    if (t <= 0 || n < 2) return +r(e[0], 0, e);
    if (t >= 1) return +r(e[n - 1], n - 1, e);
    var n, a = (n - 1) * t, i = Math.floor(a), o = +r(e[i], i, e), u = +r(e[i + 1], i + 1, e);
    return o + (u - o) * (a - i);
  }
}
function JC(e, t, r) {
  e = +e, t = +t, r = (a = arguments.length) < 2 ? (t = e, e = 0, 1) : a < 3 ? 1 : +r;
  for (var n = -1, a = Math.max(0, Math.ceil((t - e) / r)) | 0, i = new Array(a); ++n < a; )
    i[n] = e + n * r;
  return i;
}
function Ct(e, t) {
  switch (arguments.length) {
    case 0:
      break;
    case 1:
      this.range(e);
      break;
    default:
      this.range(t).domain(e);
      break;
  }
  return this;
}
function Or(e, t) {
  switch (arguments.length) {
    case 0:
      break;
    case 1: {
      typeof e == "function" ? this.interpolator(e) : this.range(e);
      break;
    }
    default: {
      this.domain(e), typeof t == "function" ? this.interpolator(t) : this.range(t);
      break;
    }
  }
  return this;
}
const $d = Symbol("implicit");
function fp() {
  var e = new _0(), t = [], r = [], n = $d;
  function a(i) {
    let o = e.get(i);
    if (o === void 0) {
      if (n !== $d) return n;
      e.set(i, o = t.push(i) - 1);
    }
    return r[o % r.length];
  }
  return a.domain = function(i) {
    if (!arguments.length) return t.slice();
    t = [], e = new _0();
    for (const o of i)
      e.has(o) || e.set(o, t.push(o) - 1);
    return a;
  }, a.range = function(i) {
    return arguments.length ? (r = Array.from(i), a) : r.slice();
  }, a.unknown = function(i) {
    return arguments.length ? (n = i, a) : n;
  }, a.copy = function() {
    return fp(t, r).unknown(n);
  }, Ct.apply(a, arguments), a;
}
function Za() {
  var e = fp().unknown(void 0), t = e.domain, r = e.range, n = 0, a = 1, i, o, u = !1, l = 0, s = 0, f = 0.5;
  delete e.unknown;
  function c() {
    var d = t().length, h = a < n, y = h ? a : n, v = h ? n : a;
    i = (v - y) / Math.max(1, d - l + s * 2), u && (i = Math.floor(i)), y += (v - y - i * (d - l)) * f, o = i * (1 - l), u && (y = Math.round(y), o = Math.round(o));
    var p = JC(d).map(function(g) {
      return y + i * g;
    });
    return r(h ? p.reverse() : p);
  }
  return e.domain = function(d) {
    return arguments.length ? (t(d), c()) : t();
  }, e.range = function(d) {
    return arguments.length ? ([n, a] = d, n = +n, a = +a, c()) : [n, a];
  }, e.rangeRound = function(d) {
    return [n, a] = d, n = +n, a = +a, u = !0, c();
  }, e.bandwidth = function() {
    return o;
  }, e.step = function() {
    return i;
  }, e.round = function(d) {
    return arguments.length ? (u = !!d, c()) : u;
  }, e.padding = function(d) {
    return arguments.length ? (l = Math.min(1, s = +d), c()) : l;
  }, e.paddingInner = function(d) {
    return arguments.length ? (l = Math.min(1, d), c()) : l;
  }, e.paddingOuter = function(d) {
    return arguments.length ? (s = +d, c()) : s;
  }, e.align = function(d) {
    return arguments.length ? (f = Math.max(0, Math.min(1, d)), c()) : f;
  }, e.copy = function() {
    return Za(t(), [n, a]).round(u).paddingInner(l).paddingOuter(s).align(f);
  }, Ct.apply(c(), arguments);
}
function yO(e) {
  var t = e.copy;
  return e.padding = e.paddingOuter, delete e.paddingInner, delete e.paddingOuter, e.copy = function() {
    return yO(t());
  }, e;
}
function Ia() {
  return yO(Za.apply(null, arguments).paddingInner(1));
}
function dp(e, t, r) {
  e.prototype = t.prototype = r, r.constructor = e;
}
function mO(e, t) {
  var r = Object.create(e.prototype);
  for (var n in t) r[n] = t[n];
  return r;
}
function Ii() {
}
var Ja = 0.7, No = 1 / Ja, Cn = "\\s*([+-]?\\d+)\\s*", Qa = "\\s*([+-]?(?:\\d*\\.)?\\d+(?:[eE][+-]?\\d+)?)\\s*", Vt = "\\s*([+-]?(?:\\d*\\.)?\\d+(?:[eE][+-]?\\d+)?)%\\s*", QC = /^#([0-9a-f]{3,8})$/, e$ = new RegExp(`^rgb\\(${Cn},${Cn},${Cn}\\)$`), t$ = new RegExp(`^rgb\\(${Vt},${Vt},${Vt}\\)$`), r$ = new RegExp(`^rgba\\(${Cn},${Cn},${Cn},${Qa}\\)$`), n$ = new RegExp(`^rgba\\(${Vt},${Vt},${Vt},${Qa}\\)$`), a$ = new RegExp(`^hsl\\(${Qa},${Vt},${Vt}\\)$`), i$ = new RegExp(`^hsla\\(${Qa},${Vt},${Vt},${Qa}\\)$`), E0 = {
  aliceblue: 15792383,
  antiquewhite: 16444375,
  aqua: 65535,
  aquamarine: 8388564,
  azure: 15794175,
  beige: 16119260,
  bisque: 16770244,
  black: 0,
  blanchedalmond: 16772045,
  blue: 255,
  blueviolet: 9055202,
  brown: 10824234,
  burlywood: 14596231,
  cadetblue: 6266528,
  chartreuse: 8388352,
  chocolate: 13789470,
  coral: 16744272,
  cornflowerblue: 6591981,
  cornsilk: 16775388,
  crimson: 14423100,
  cyan: 65535,
  darkblue: 139,
  darkcyan: 35723,
  darkgoldenrod: 12092939,
  darkgray: 11119017,
  darkgreen: 25600,
  darkgrey: 11119017,
  darkkhaki: 12433259,
  darkmagenta: 9109643,
  darkolivegreen: 5597999,
  darkorange: 16747520,
  darkorchid: 10040012,
  darkred: 9109504,
  darksalmon: 15308410,
  darkseagreen: 9419919,
  darkslateblue: 4734347,
  darkslategray: 3100495,
  darkslategrey: 3100495,
  darkturquoise: 52945,
  darkviolet: 9699539,
  deeppink: 16716947,
  deepskyblue: 49151,
  dimgray: 6908265,
  dimgrey: 6908265,
  dodgerblue: 2003199,
  firebrick: 11674146,
  floralwhite: 16775920,
  forestgreen: 2263842,
  fuchsia: 16711935,
  gainsboro: 14474460,
  ghostwhite: 16316671,
  gold: 16766720,
  goldenrod: 14329120,
  gray: 8421504,
  green: 32768,
  greenyellow: 11403055,
  grey: 8421504,
  honeydew: 15794160,
  hotpink: 16738740,
  indianred: 13458524,
  indigo: 4915330,
  ivory: 16777200,
  khaki: 15787660,
  lavender: 15132410,
  lavenderblush: 16773365,
  lawngreen: 8190976,
  lemonchiffon: 16775885,
  lightblue: 11393254,
  lightcoral: 15761536,
  lightcyan: 14745599,
  lightgoldenrodyellow: 16448210,
  lightgray: 13882323,
  lightgreen: 9498256,
  lightgrey: 13882323,
  lightpink: 16758465,
  lightsalmon: 16752762,
  lightseagreen: 2142890,
  lightskyblue: 8900346,
  lightslategray: 7833753,
  lightslategrey: 7833753,
  lightsteelblue: 11584734,
  lightyellow: 16777184,
  lime: 65280,
  limegreen: 3329330,
  linen: 16445670,
  magenta: 16711935,
  maroon: 8388608,
  mediumaquamarine: 6737322,
  mediumblue: 205,
  mediumorchid: 12211667,
  mediumpurple: 9662683,
  mediumseagreen: 3978097,
  mediumslateblue: 8087790,
  mediumspringgreen: 64154,
  mediumturquoise: 4772300,
  mediumvioletred: 13047173,
  midnightblue: 1644912,
  mintcream: 16121850,
  mistyrose: 16770273,
  moccasin: 16770229,
  navajowhite: 16768685,
  navy: 128,
  oldlace: 16643558,
  olive: 8421376,
  olivedrab: 7048739,
  orange: 16753920,
  orangered: 16729344,
  orchid: 14315734,
  palegoldenrod: 15657130,
  palegreen: 10025880,
  paleturquoise: 11529966,
  palevioletred: 14381203,
  papayawhip: 16773077,
  peachpuff: 16767673,
  peru: 13468991,
  pink: 16761035,
  plum: 14524637,
  powderblue: 11591910,
  purple: 8388736,
  rebeccapurple: 6697881,
  red: 16711680,
  rosybrown: 12357519,
  royalblue: 4286945,
  saddlebrown: 9127187,
  salmon: 16416882,
  sandybrown: 16032864,
  seagreen: 3050327,
  seashell: 16774638,
  sienna: 10506797,
  silver: 12632256,
  skyblue: 8900331,
  slateblue: 6970061,
  slategray: 7372944,
  slategrey: 7372944,
  snow: 16775930,
  springgreen: 65407,
  steelblue: 4620980,
  tan: 13808780,
  teal: 32896,
  thistle: 14204888,
  tomato: 16737095,
  turquoise: 4251856,
  violet: 15631086,
  wheat: 16113331,
  white: 16777215,
  whitesmoke: 16119285,
  yellow: 16776960,
  yellowgreen: 10145074
};
dp(Ii, ei, {
  copy(e) {
    return Object.assign(new this.constructor(), this, e);
  },
  displayable() {
    return this.rgb().displayable();
  },
  hex: T0,
  // Deprecated! Use color.formatHex.
  formatHex: T0,
  formatHex8: o$,
  formatHsl: u$,
  formatRgb: M0,
  toString: M0
});
function T0() {
  return this.rgb().formatHex();
}
function o$() {
  return this.rgb().formatHex8();
}
function u$() {
  return gO(this).formatHsl();
}
function M0() {
  return this.rgb().formatRgb();
}
function ei(e) {
  var t, r;
  return e = (e + "").trim().toLowerCase(), (t = QC.exec(e)) ? (r = t[1].length, t = parseInt(t[1], 16), r === 6 ? j0(t) : r === 3 ? new dt(t >> 8 & 15 | t >> 4 & 240, t >> 4 & 15 | t & 240, (t & 15) << 4 | t & 15, 1) : r === 8 ? eo(t >> 24 & 255, t >> 16 & 255, t >> 8 & 255, (t & 255) / 255) : r === 4 ? eo(t >> 12 & 15 | t >> 8 & 240, t >> 8 & 15 | t >> 4 & 240, t >> 4 & 15 | t & 240, ((t & 15) << 4 | t & 15) / 255) : null) : (t = e$.exec(e)) ? new dt(t[1], t[2], t[3], 1) : (t = t$.exec(e)) ? new dt(t[1] * 255 / 100, t[2] * 255 / 100, t[3] * 255 / 100, 1) : (t = r$.exec(e)) ? eo(t[1], t[2], t[3], t[4]) : (t = n$.exec(e)) ? eo(t[1] * 255 / 100, t[2] * 255 / 100, t[3] * 255 / 100, t[4]) : (t = a$.exec(e)) ? $0(t[1], t[2] / 100, t[3] / 100, 1) : (t = i$.exec(e)) ? $0(t[1], t[2] / 100, t[3] / 100, t[4]) : E0.hasOwnProperty(e) ? j0(E0[e]) : e === "transparent" ? new dt(NaN, NaN, NaN, 0) : null;
}
function j0(e) {
  return new dt(e >> 16 & 255, e >> 8 & 255, e & 255, 1);
}
function eo(e, t, r, n) {
  return n <= 0 && (e = t = r = NaN), new dt(e, t, r, n);
}
function l$(e) {
  return e instanceof Ii || (e = ei(e)), e ? (e = e.rgb(), new dt(e.r, e.g, e.b, e.opacity)) : new dt();
}
function Rd(e, t, r, n) {
  return arguments.length === 1 ? l$(e) : new dt(e, t, r, n ?? 1);
}
function dt(e, t, r, n) {
  this.r = +e, this.g = +t, this.b = +r, this.opacity = +n;
}
dp(dt, Rd, mO(Ii, {
  brighter(e) {
    return e = e == null ? No : Math.pow(No, e), new dt(this.r * e, this.g * e, this.b * e, this.opacity);
  },
  darker(e) {
    return e = e == null ? Ja : Math.pow(Ja, e), new dt(this.r * e, this.g * e, this.b * e, this.opacity);
  },
  rgb() {
    return this;
  },
  clamp() {
    return new dt(nn(this.r), nn(this.g), nn(this.b), Co(this.opacity));
  },
  displayable() {
    return -0.5 <= this.r && this.r < 255.5 && -0.5 <= this.g && this.g < 255.5 && -0.5 <= this.b && this.b < 255.5 && 0 <= this.opacity && this.opacity <= 1;
  },
  hex: N0,
  // Deprecated! Use color.formatHex.
  formatHex: N0,
  formatHex8: s$,
  formatRgb: C0,
  toString: C0
}));
function N0() {
  return `#${Jr(this.r)}${Jr(this.g)}${Jr(this.b)}`;
}
function s$() {
  return `#${Jr(this.r)}${Jr(this.g)}${Jr(this.b)}${Jr((isNaN(this.opacity) ? 1 : this.opacity) * 255)}`;
}
function C0() {
  const e = Co(this.opacity);
  return `${e === 1 ? "rgb(" : "rgba("}${nn(this.r)}, ${nn(this.g)}, ${nn(this.b)}${e === 1 ? ")" : `, ${e})`}`;
}
function Co(e) {
  return isNaN(e) ? 1 : Math.max(0, Math.min(1, e));
}
function nn(e) {
  return Math.max(0, Math.min(255, Math.round(e) || 0));
}
function Jr(e) {
  return e = nn(e), (e < 16 ? "0" : "") + e.toString(16);
}
function $0(e, t, r, n) {
  return n <= 0 ? e = t = r = NaN : r <= 0 || r >= 1 ? e = t = NaN : t <= 0 && (e = NaN), new kt(e, t, r, n);
}
function gO(e) {
  if (e instanceof kt) return new kt(e.h, e.s, e.l, e.opacity);
  if (e instanceof Ii || (e = ei(e)), !e) return new kt();
  if (e instanceof kt) return e;
  e = e.rgb();
  var t = e.r / 255, r = e.g / 255, n = e.b / 255, a = Math.min(t, r, n), i = Math.max(t, r, n), o = NaN, u = i - a, l = (i + a) / 2;
  return u ? (t === i ? o = (r - n) / u + (r < n) * 6 : r === i ? o = (n - t) / u + 2 : o = (t - r) / u + 4, u /= l < 0.5 ? i + a : 2 - i - a, o *= 60) : u = l > 0 && l < 1 ? 0 : o, new kt(o, u, l, e.opacity);
}
function c$(e, t, r, n) {
  return arguments.length === 1 ? gO(e) : new kt(e, t, r, n ?? 1);
}
function kt(e, t, r, n) {
  this.h = +e, this.s = +t, this.l = +r, this.opacity = +n;
}
dp(kt, c$, mO(Ii, {
  brighter(e) {
    return e = e == null ? No : Math.pow(No, e), new kt(this.h, this.s, this.l * e, this.opacity);
  },
  darker(e) {
    return e = e == null ? Ja : Math.pow(Ja, e), new kt(this.h, this.s, this.l * e, this.opacity);
  },
  rgb() {
    var e = this.h % 360 + (this.h < 0) * 360, t = isNaN(e) || isNaN(this.s) ? 0 : this.s, r = this.l, n = r + (r < 0.5 ? r : 1 - r) * t, a = 2 * r - n;
    return new dt(
      df(e >= 240 ? e - 240 : e + 120, a, n),
      df(e, a, n),
      df(e < 120 ? e + 240 : e - 120, a, n),
      this.opacity
    );
  },
  clamp() {
    return new kt(R0(this.h), to(this.s), to(this.l), Co(this.opacity));
  },
  displayable() {
    return (0 <= this.s && this.s <= 1 || isNaN(this.s)) && 0 <= this.l && this.l <= 1 && 0 <= this.opacity && this.opacity <= 1;
  },
  formatHsl() {
    const e = Co(this.opacity);
    return `${e === 1 ? "hsl(" : "hsla("}${R0(this.h)}, ${to(this.s) * 100}%, ${to(this.l) * 100}%${e === 1 ? ")" : `, ${e})`}`;
  }
}));
function R0(e) {
  return e = (e || 0) % 360, e < 0 ? e + 360 : e;
}
function to(e) {
  return Math.max(0, Math.min(1, e || 0));
}
function df(e, t, r) {
  return (e < 60 ? t + (r - t) * e / 60 : e < 180 ? r : e < 240 ? t + (r - t) * (240 - e) / 60 : t) * 255;
}
const hp = (e) => () => e;
function f$(e, t) {
  return function(r) {
    return e + r * t;
  };
}
function d$(e, t, r) {
  return e = Math.pow(e, r), t = Math.pow(t, r) - e, r = 1 / r, function(n) {
    return Math.pow(e + n * t, r);
  };
}
function h$(e) {
  return (e = +e) == 1 ? bO : function(t, r) {
    return r - t ? d$(t, r, e) : hp(isNaN(t) ? r : t);
  };
}
function bO(e, t) {
  var r = t - e;
  return r ? f$(e, r) : hp(isNaN(e) ? t : e);
}
const k0 = (function e(t) {
  var r = h$(t);
  function n(a, i) {
    var o = r((a = Rd(a)).r, (i = Rd(i)).r), u = r(a.g, i.g), l = r(a.b, i.b), s = bO(a.opacity, i.opacity);
    return function(f) {
      return a.r = o(f), a.g = u(f), a.b = l(f), a.opacity = s(f), a + "";
    };
  }
  return n.gamma = e, n;
})(1);
function p$(e, t) {
  t || (t = []);
  var r = e ? Math.min(t.length, e.length) : 0, n = t.slice(), a;
  return function(i) {
    for (a = 0; a < r; ++a) n[a] = e[a] * (1 - i) + t[a] * i;
    return n;
  };
}
function v$(e) {
  return ArrayBuffer.isView(e) && !(e instanceof DataView);
}
function y$(e, t) {
  var r = t ? t.length : 0, n = e ? Math.min(r, e.length) : 0, a = new Array(n), i = new Array(r), o;
  for (o = 0; o < n; ++o) a[o] = la(e[o], t[o]);
  for (; o < r; ++o) i[o] = t[o];
  return function(u) {
    for (o = 0; o < n; ++o) i[o] = a[o](u);
    return i;
  };
}
function m$(e, t) {
  var r = /* @__PURE__ */ new Date();
  return e = +e, t = +t, function(n) {
    return r.setTime(e * (1 - n) + t * n), r;
  };
}
function $o(e, t) {
  return e = +e, t = +t, function(r) {
    return e * (1 - r) + t * r;
  };
}
function g$(e, t) {
  var r = {}, n = {}, a;
  (e === null || typeof e != "object") && (e = {}), (t === null || typeof t != "object") && (t = {});
  for (a in t)
    a in e ? r[a] = la(e[a], t[a]) : n[a] = t[a];
  return function(i) {
    for (a in r) n[a] = r[a](i);
    return n;
  };
}
var kd = /[-+]?(?:\d+\.?\d*|\.?\d+)(?:[eE][-+]?\d+)?/g, hf = new RegExp(kd.source, "g");
function b$(e) {
  return function() {
    return e;
  };
}
function x$(e) {
  return function(t) {
    return e(t) + "";
  };
}
function w$(e, t) {
  var r = kd.lastIndex = hf.lastIndex = 0, n, a, i, o = -1, u = [], l = [];
  for (e = e + "", t = t + ""; (n = kd.exec(e)) && (a = hf.exec(t)); )
    (i = a.index) > r && (i = t.slice(r, i), u[o] ? u[o] += i : u[++o] = i), (n = n[0]) === (a = a[0]) ? u[o] ? u[o] += a : u[++o] = a : (u[++o] = null, l.push({ i: o, x: $o(n, a) })), r = hf.lastIndex;
  return r < t.length && (i = t.slice(r), u[o] ? u[o] += i : u[++o] = i), u.length < 2 ? l[0] ? x$(l[0].x) : b$(t) : (t = l.length, function(s) {
    for (var f = 0, c; f < t; ++f) u[(c = l[f]).i] = c.x(s);
    return u.join("");
  });
}
function la(e, t) {
  var r = typeof t, n;
  return t == null || r === "boolean" ? hp(t) : (r === "number" ? $o : r === "string" ? (n = ei(t)) ? (t = n, k0) : w$ : t instanceof ei ? k0 : t instanceof Date ? m$ : v$(t) ? p$ : Array.isArray(t) ? y$ : typeof t.valueOf != "function" && typeof t.toString != "function" || isNaN(t) ? g$ : $o)(e, t);
}
function pp(e, t) {
  return e = +e, t = +t, function(r) {
    return Math.round(e * (1 - r) + t * r);
  };
}
function O$(e, t) {
  t === void 0 && (t = e, e = la);
  for (var r = 0, n = t.length - 1, a = t[0], i = new Array(n < 0 ? 0 : n); r < n; ) i[r] = e(a, a = t[++r]);
  return function(o) {
    var u = Math.max(0, Math.min(n - 1, Math.floor(o *= n)));
    return i[u](o - u);
  };
}
function _$(e) {
  return function() {
    return e;
  };
}
function Ro(e) {
  return +e;
}
var I0 = [0, 1];
function ct(e) {
  return e;
}
function Id(e, t) {
  return (t -= e = +e) ? function(r) {
    return (r - e) / t;
  } : _$(isNaN(t) ? NaN : 0.5);
}
function S$(e, t) {
  var r;
  return e > t && (r = e, e = t, t = r), function(n) {
    return Math.max(e, Math.min(t, n));
  };
}
function P$(e, t, r) {
  var n = e[0], a = e[1], i = t[0], o = t[1];
  return a < n ? (n = Id(a, n), i = r(o, i)) : (n = Id(n, a), i = r(i, o)), function(u) {
    return i(n(u));
  };
}
function A$(e, t, r) {
  var n = Math.min(e.length, t.length) - 1, a = new Array(n), i = new Array(n), o = -1;
  for (e[n] < e[0] && (e = e.slice().reverse(), t = t.slice().reverse()); ++o < n; )
    a[o] = Id(e[o], e[o + 1]), i[o] = r(t[o], t[o + 1]);
  return function(u) {
    var l = ki(e, u, 1, n) - 1;
    return i[l](a[l](u));
  };
}
function Di(e, t) {
  return t.domain(e.domain()).range(e.range()).interpolate(e.interpolate()).clamp(e.clamp()).unknown(e.unknown());
}
function Mu() {
  var e = I0, t = I0, r = la, n, a, i, o = ct, u, l, s;
  function f() {
    var d = Math.min(e.length, t.length);
    return o !== ct && (o = S$(e[0], e[d - 1])), u = d > 2 ? A$ : P$, l = s = null, c;
  }
  function c(d) {
    return d == null || isNaN(d = +d) ? i : (l || (l = u(e.map(n), t, r)))(n(o(d)));
  }
  return c.invert = function(d) {
    return o(a((s || (s = u(t, e.map(n), $o)))(d)));
  }, c.domain = function(d) {
    return arguments.length ? (e = Array.from(d, Ro), f()) : e.slice();
  }, c.range = function(d) {
    return arguments.length ? (t = Array.from(d), f()) : t.slice();
  }, c.rangeRound = function(d) {
    return t = Array.from(d), r = pp, f();
  }, c.clamp = function(d) {
    return arguments.length ? (o = d ? !0 : ct, f()) : o !== ct;
  }, c.interpolate = function(d) {
    return arguments.length ? (r = d, f()) : r;
  }, c.unknown = function(d) {
    return arguments.length ? (i = d, c) : i;
  }, function(d, h) {
    return n = d, a = h, f();
  };
}
function vp() {
  return Mu()(ct, ct);
}
function E$(e) {
  return Math.abs(e = Math.round(e)) >= 1e21 ? e.toLocaleString("en").replace(/,/g, "") : e.toString(10);
}
function ko(e, t) {
  if ((r = (e = t ? e.toExponential(t - 1) : e.toExponential()).indexOf("e")) < 0) return null;
  var r, n = e.slice(0, r);
  return [
    n.length > 1 ? n[0] + n.slice(2) : n,
    +e.slice(r + 1)
  ];
}
function Bn(e) {
  return e = ko(Math.abs(e)), e ? e[1] : NaN;
}
function T$(e, t) {
  return function(r, n) {
    for (var a = r.length, i = [], o = 0, u = e[0], l = 0; a > 0 && u > 0 && (l + u + 1 > n && (u = Math.max(1, n - l)), i.push(r.substring(a -= u, a + u)), !((l += u + 1) > n)); )
      u = e[o = (o + 1) % e.length];
    return i.reverse().join(t);
  };
}
function M$(e) {
  return function(t) {
    return t.replace(/[0-9]/g, function(r) {
      return e[+r];
    });
  };
}
var j$ = /^(?:(.)?([<>=^]))?([+\-( ])?([$#])?(0)?(\d+)?(,)?(\.\d+)?(~)?([a-z%])?$/i;
function ti(e) {
  if (!(t = j$.exec(e))) throw new Error("invalid format: " + e);
  var t;
  return new yp({
    fill: t[1],
    align: t[2],
    sign: t[3],
    symbol: t[4],
    zero: t[5],
    width: t[6],
    comma: t[7],
    precision: t[8] && t[8].slice(1),
    trim: t[9],
    type: t[10]
  });
}
ti.prototype = yp.prototype;
function yp(e) {
  this.fill = e.fill === void 0 ? " " : e.fill + "", this.align = e.align === void 0 ? ">" : e.align + "", this.sign = e.sign === void 0 ? "-" : e.sign + "", this.symbol = e.symbol === void 0 ? "" : e.symbol + "", this.zero = !!e.zero, this.width = e.width === void 0 ? void 0 : +e.width, this.comma = !!e.comma, this.precision = e.precision === void 0 ? void 0 : +e.precision, this.trim = !!e.trim, this.type = e.type === void 0 ? "" : e.type + "";
}
yp.prototype.toString = function() {
  return this.fill + this.align + this.sign + this.symbol + (this.zero ? "0" : "") + (this.width === void 0 ? "" : Math.max(1, this.width | 0)) + (this.comma ? "," : "") + (this.precision === void 0 ? "" : "." + Math.max(0, this.precision | 0)) + (this.trim ? "~" : "") + this.type;
};
function N$(e) {
  e: for (var t = e.length, r = 1, n = -1, a; r < t; ++r)
    switch (e[r]) {
      case ".":
        n = a = r;
        break;
      case "0":
        n === 0 && (n = r), a = r;
        break;
      default:
        if (!+e[r]) break e;
        n > 0 && (n = 0);
        break;
    }
  return n > 0 ? e.slice(0, n) + e.slice(a + 1) : e;
}
var xO;
function C$(e, t) {
  var r = ko(e, t);
  if (!r) return e + "";
  var n = r[0], a = r[1], i = a - (xO = Math.max(-8, Math.min(8, Math.floor(a / 3))) * 3) + 1, o = n.length;
  return i === o ? n : i > o ? n + new Array(i - o + 1).join("0") : i > 0 ? n.slice(0, i) + "." + n.slice(i) : "0." + new Array(1 - i).join("0") + ko(e, Math.max(0, t + i - 1))[0];
}
function D0(e, t) {
  var r = ko(e, t);
  if (!r) return e + "";
  var n = r[0], a = r[1];
  return a < 0 ? "0." + new Array(-a).join("0") + n : n.length > a + 1 ? n.slice(0, a + 1) + "." + n.slice(a + 1) : n + new Array(a - n.length + 2).join("0");
}
const L0 = {
  "%": (e, t) => (e * 100).toFixed(t),
  b: (e) => Math.round(e).toString(2),
  c: (e) => e + "",
  d: E$,
  e: (e, t) => e.toExponential(t),
  f: (e, t) => e.toFixed(t),
  g: (e, t) => e.toPrecision(t),
  o: (e) => Math.round(e).toString(8),
  p: (e, t) => D0(e * 100, t),
  r: D0,
  s: C$,
  X: (e) => Math.round(e).toString(16).toUpperCase(),
  x: (e) => Math.round(e).toString(16)
};
function q0(e) {
  return e;
}
var B0 = Array.prototype.map, F0 = ["y", "z", "a", "f", "p", "n", "µ", "m", "", "k", "M", "G", "T", "P", "E", "Z", "Y"];
function $$(e) {
  var t = e.grouping === void 0 || e.thousands === void 0 ? q0 : T$(B0.call(e.grouping, Number), e.thousands + ""), r = e.currency === void 0 ? "" : e.currency[0] + "", n = e.currency === void 0 ? "" : e.currency[1] + "", a = e.decimal === void 0 ? "." : e.decimal + "", i = e.numerals === void 0 ? q0 : M$(B0.call(e.numerals, String)), o = e.percent === void 0 ? "%" : e.percent + "", u = e.minus === void 0 ? "−" : e.minus + "", l = e.nan === void 0 ? "NaN" : e.nan + "";
  function s(c) {
    c = ti(c);
    var d = c.fill, h = c.align, y = c.sign, v = c.symbol, p = c.zero, g = c.width, b = c.comma, w = c.precision, _ = c.trim, m = c.type;
    m === "n" ? (b = !0, m = "g") : L0[m] || (w === void 0 && (w = 12), _ = !0, m = "g"), (p || d === "0" && h === "=") && (p = !0, d = "0", h = "=");
    var O = v === "$" ? r : v === "#" && /[boxX]/.test(m) ? "0" + m.toLowerCase() : "", x = v === "$" ? n : /[%p]/.test(m) ? o : "", S = L0[m], T = /[defgprs%]/.test(m);
    w = w === void 0 ? 6 : /[gprs]/.test(m) ? Math.max(1, Math.min(21, w)) : Math.max(0, Math.min(20, w));
    function C(A) {
      var N = O, $ = x, D, R, L;
      if (m === "c")
        $ = S(A) + $, A = "";
      else {
        A = +A;
        var z = A < 0 || 1 / A < 0;
        if (A = isNaN(A) ? l : S(Math.abs(A), w), _ && (A = N$(A)), z && +A == 0 && y !== "+" && (z = !1), N = (z ? y === "(" ? y : u : y === "-" || y === "(" ? "" : y) + N, $ = (m === "s" ? F0[8 + xO / 3] : "") + $ + (z && y === "(" ? ")" : ""), T) {
          for (D = -1, R = A.length; ++D < R; )
            if (L = A.charCodeAt(D), 48 > L || L > 57) {
              $ = (L === 46 ? a + A.slice(D + 1) : A.slice(D)) + $, A = A.slice(0, D);
              break;
            }
        }
      }
      b && !p && (A = t(A, 1 / 0));
      var F = N.length + A.length + $.length, W = F < g ? new Array(g - F + 1).join(d) : "";
      switch (b && p && (A = t(W + A, W.length ? g - $.length : 1 / 0), W = ""), h) {
        case "<":
          A = N + A + $ + W;
          break;
        case "=":
          A = N + W + A + $;
          break;
        case "^":
          A = W.slice(0, F = W.length >> 1) + N + A + $ + W.slice(F);
          break;
        default:
          A = W + N + A + $;
          break;
      }
      return i(A);
    }
    return C.toString = function() {
      return c + "";
    }, C;
  }
  function f(c, d) {
    var h = s((c = ti(c), c.type = "f", c)), y = Math.max(-8, Math.min(8, Math.floor(Bn(d) / 3))) * 3, v = Math.pow(10, -y), p = F0[8 + y / 3];
    return function(g) {
      return h(v * g) + p;
    };
  }
  return {
    format: s,
    formatPrefix: f
  };
}
var ro, mp, wO;
R$({
  thousands: ",",
  grouping: [3],
  currency: ["$", ""]
});
function R$(e) {
  return ro = $$(e), mp = ro.format, wO = ro.formatPrefix, ro;
}
function k$(e) {
  return Math.max(0, -Bn(Math.abs(e)));
}
function I$(e, t) {
  return Math.max(0, Math.max(-8, Math.min(8, Math.floor(Bn(t) / 3))) * 3 - Bn(Math.abs(e)));
}
function D$(e, t) {
  return e = Math.abs(e), t = Math.abs(t) - e, Math.max(0, Bn(t) - Bn(e)) + 1;
}
function OO(e, t, r, n) {
  var a = Cd(e, t, r), i;
  switch (n = ti(n ?? ",f"), n.type) {
    case "s": {
      var o = Math.max(Math.abs(e), Math.abs(t));
      return n.precision == null && !isNaN(i = I$(a, o)) && (n.precision = i), wO(n, o);
    }
    case "":
    case "e":
    case "g":
    case "p":
    case "r": {
      n.precision == null && !isNaN(i = D$(a, Math.max(Math.abs(e), Math.abs(t)))) && (n.precision = i - (n.type === "e"));
      break;
    }
    case "f":
    case "%": {
      n.precision == null && !isNaN(i = k$(a)) && (n.precision = i - (n.type === "%") * 2);
      break;
    }
  }
  return mp(n);
}
function Br(e) {
  var t = e.domain;
  return e.ticks = function(r) {
    var n = t();
    return jd(n[0], n[n.length - 1], r ?? 10);
  }, e.tickFormat = function(r, n) {
    var a = t();
    return OO(a[0], a[a.length - 1], r ?? 10, n);
  }, e.nice = function(r) {
    r == null && (r = 10);
    var n = t(), a = 0, i = n.length - 1, o = n[a], u = n[i], l, s, f = 10;
    for (u < o && (s = o, o = u, u = s, s = a, a = i, i = s); f-- > 0; ) {
      if (s = Nd(o, u, r), s === l)
        return n[a] = o, n[i] = u, t(n);
      if (s > 0)
        o = Math.floor(o / s) * s, u = Math.ceil(u / s) * s;
      else if (s < 0)
        o = Math.ceil(o * s) / s, u = Math.floor(u * s) / s;
      else
        break;
      l = s;
    }
    return e;
  }, e;
}
function Io() {
  var e = vp();
  return e.copy = function() {
    return Di(e, Io());
  }, Ct.apply(e, arguments), Br(e);
}
function _O(e) {
  var t;
  function r(n) {
    return n == null || isNaN(n = +n) ? t : n;
  }
  return r.invert = r, r.domain = r.range = function(n) {
    return arguments.length ? (e = Array.from(n, Ro), r) : e.slice();
  }, r.unknown = function(n) {
    return arguments.length ? (t = n, r) : t;
  }, r.copy = function() {
    return _O(e).unknown(t);
  }, e = arguments.length ? Array.from(e, Ro) : [0, 1], Br(r);
}
function SO(e, t) {
  e = e.slice();
  var r = 0, n = e.length - 1, a = e[r], i = e[n], o;
  return i < a && (o = r, r = n, n = o, o = a, a = i, i = o), e[r] = t.floor(a), e[n] = t.ceil(i), e;
}
function z0(e) {
  return Math.log(e);
}
function U0(e) {
  return Math.exp(e);
}
function L$(e) {
  return -Math.log(-e);
}
function q$(e) {
  return -Math.exp(-e);
}
function B$(e) {
  return isFinite(e) ? +("1e" + e) : e < 0 ? 0 : e;
}
function F$(e) {
  return e === 10 ? B$ : e === Math.E ? Math.exp : (t) => Math.pow(e, t);
}
function z$(e) {
  return e === Math.E ? Math.log : e === 10 && Math.log10 || e === 2 && Math.log2 || (e = Math.log(e), (t) => Math.log(t) / e);
}
function W0(e) {
  return (t, r) => -e(-t, r);
}
function gp(e) {
  const t = e(z0, U0), r = t.domain;
  let n = 10, a, i;
  function o() {
    return a = z$(n), i = F$(n), r()[0] < 0 ? (a = W0(a), i = W0(i), e(L$, q$)) : e(z0, U0), t;
  }
  return t.base = function(u) {
    return arguments.length ? (n = +u, o()) : n;
  }, t.domain = function(u) {
    return arguments.length ? (r(u), o()) : r();
  }, t.ticks = (u) => {
    const l = r();
    let s = l[0], f = l[l.length - 1];
    const c = f < s;
    c && ([s, f] = [f, s]);
    let d = a(s), h = a(f), y, v;
    const p = u == null ? 10 : +u;
    let g = [];
    if (!(n % 1) && h - d < p) {
      if (d = Math.floor(d), h = Math.ceil(h), s > 0) {
        for (; d <= h; ++d)
          for (y = 1; y < n; ++y)
            if (v = d < 0 ? y / i(-d) : y * i(d), !(v < s)) {
              if (v > f) break;
              g.push(v);
            }
      } else for (; d <= h; ++d)
        for (y = n - 1; y >= 1; --y)
          if (v = d > 0 ? y / i(-d) : y * i(d), !(v < s)) {
            if (v > f) break;
            g.push(v);
          }
      g.length * 2 < p && (g = jd(s, f, p));
    } else
      g = jd(d, h, Math.min(h - d, p)).map(i);
    return c ? g.reverse() : g;
  }, t.tickFormat = (u, l) => {
    if (u == null && (u = 10), l == null && (l = n === 10 ? "s" : ","), typeof l != "function" && (!(n % 1) && (l = ti(l)).precision == null && (l.trim = !0), l = mp(l)), u === 1 / 0) return l;
    const s = Math.max(1, n * u / t.ticks().length);
    return (f) => {
      let c = f / i(Math.round(a(f)));
      return c * n < n - 0.5 && (c *= n), c <= s ? l(f) : "";
    };
  }, t.nice = () => r(SO(r(), {
    floor: (u) => i(Math.floor(a(u))),
    ceil: (u) => i(Math.ceil(a(u)))
  })), t;
}
function PO() {
  const e = gp(Mu()).domain([1, 10]);
  return e.copy = () => Di(e, PO()).base(e.base()), Ct.apply(e, arguments), e;
}
function H0(e) {
  return function(t) {
    return Math.sign(t) * Math.log1p(Math.abs(t / e));
  };
}
function G0(e) {
  return function(t) {
    return Math.sign(t) * Math.expm1(Math.abs(t)) * e;
  };
}
function bp(e) {
  var t = 1, r = e(H0(t), G0(t));
  return r.constant = function(n) {
    return arguments.length ? e(H0(t = +n), G0(t)) : t;
  }, Br(r);
}
function AO() {
  var e = bp(Mu());
  return e.copy = function() {
    return Di(e, AO()).constant(e.constant());
  }, Ct.apply(e, arguments);
}
function K0(e) {
  return function(t) {
    return t < 0 ? -Math.pow(-t, e) : Math.pow(t, e);
  };
}
function U$(e) {
  return e < 0 ? -Math.sqrt(-e) : Math.sqrt(e);
}
function W$(e) {
  return e < 0 ? -e * e : e * e;
}
function xp(e) {
  var t = e(ct, ct), r = 1;
  function n() {
    return r === 1 ? e(ct, ct) : r === 0.5 ? e(U$, W$) : e(K0(r), K0(1 / r));
  }
  return t.exponent = function(a) {
    return arguments.length ? (r = +a, n()) : r;
  }, Br(t);
}
function wp() {
  var e = xp(Mu());
  return e.copy = function() {
    return Di(e, wp()).exponent(e.exponent());
  }, Ct.apply(e, arguments), e;
}
function H$() {
  return wp.apply(null, arguments).exponent(0.5);
}
function V0(e) {
  return Math.sign(e) * e * e;
}
function G$(e) {
  return Math.sign(e) * Math.sqrt(Math.abs(e));
}
function EO() {
  var e = vp(), t = [0, 1], r = !1, n;
  function a(i) {
    var o = G$(e(i));
    return isNaN(o) ? n : r ? Math.round(o) : o;
  }
  return a.invert = function(i) {
    return e.invert(V0(i));
  }, a.domain = function(i) {
    return arguments.length ? (e.domain(i), a) : e.domain();
  }, a.range = function(i) {
    return arguments.length ? (e.range((t = Array.from(i, Ro)).map(V0)), a) : t.slice();
  }, a.rangeRound = function(i) {
    return a.range(i).round(!0);
  }, a.round = function(i) {
    return arguments.length ? (r = !!i, a) : r;
  }, a.clamp = function(i) {
    return arguments.length ? (e.clamp(i), a) : e.clamp();
  }, a.unknown = function(i) {
    return arguments.length ? (n = i, a) : n;
  }, a.copy = function() {
    return EO(e.domain(), t).round(r).clamp(e.clamp()).unknown(n);
  }, Ct.apply(a, arguments), Br(a);
}
function TO() {
  var e = [], t = [], r = [], n;
  function a() {
    var o = 0, u = Math.max(1, t.length);
    for (r = new Array(u - 1); ++o < u; ) r[o - 1] = ZC(e, o / u);
    return i;
  }
  function i(o) {
    return o == null || isNaN(o = +o) ? n : t[ki(r, o)];
  }
  return i.invertExtent = function(o) {
    var u = t.indexOf(o);
    return u < 0 ? [NaN, NaN] : [
      u > 0 ? r[u - 1] : e[0],
      u < r.length ? r[u] : e[e.length - 1]
    ];
  }, i.domain = function(o) {
    if (!arguments.length) return e.slice();
    e = [];
    for (let u of o) u != null && !isNaN(u = +u) && e.push(u);
    return e.sort(Rr), a();
  }, i.range = function(o) {
    return arguments.length ? (t = Array.from(o), a()) : t.slice();
  }, i.unknown = function(o) {
    return arguments.length ? (n = o, i) : n;
  }, i.quantiles = function() {
    return r.slice();
  }, i.copy = function() {
    return TO().domain(e).range(t).unknown(n);
  }, Ct.apply(i, arguments);
}
function MO() {
  var e = 0, t = 1, r = 1, n = [0.5], a = [0, 1], i;
  function o(l) {
    return l != null && l <= l ? a[ki(n, l, 0, r)] : i;
  }
  function u() {
    var l = -1;
    for (n = new Array(r); ++l < r; ) n[l] = ((l + 1) * t - (l - r) * e) / (r + 1);
    return o;
  }
  return o.domain = function(l) {
    return arguments.length ? ([e, t] = l, e = +e, t = +t, u()) : [e, t];
  }, o.range = function(l) {
    return arguments.length ? (r = (a = Array.from(l)).length - 1, u()) : a.slice();
  }, o.invertExtent = function(l) {
    var s = a.indexOf(l);
    return s < 0 ? [NaN, NaN] : s < 1 ? [e, n[0]] : s >= r ? [n[r - 1], t] : [n[s - 1], n[s]];
  }, o.unknown = function(l) {
    return arguments.length && (i = l), o;
  }, o.thresholds = function() {
    return n.slice();
  }, o.copy = function() {
    return MO().domain([e, t]).range(a).unknown(i);
  }, Ct.apply(Br(o), arguments);
}
function jO() {
  var e = [0.5], t = [0, 1], r, n = 1;
  function a(i) {
    return i != null && i <= i ? t[ki(e, i, 0, n)] : r;
  }
  return a.domain = function(i) {
    return arguments.length ? (e = Array.from(i), n = Math.min(e.length, t.length - 1), a) : e.slice();
  }, a.range = function(i) {
    return arguments.length ? (t = Array.from(i), n = Math.min(e.length, t.length - 1), a) : t.slice();
  }, a.invertExtent = function(i) {
    var o = t.indexOf(i);
    return [e[o - 1], e[o]];
  }, a.unknown = function(i) {
    return arguments.length ? (r = i, a) : r;
  }, a.copy = function() {
    return jO().domain(e).range(t).unknown(r);
  }, Ct.apply(a, arguments);
}
const pf = /* @__PURE__ */ new Date(), vf = /* @__PURE__ */ new Date();
function Xe(e, t, r, n) {
  function a(i) {
    return e(i = arguments.length === 0 ? /* @__PURE__ */ new Date() : /* @__PURE__ */ new Date(+i)), i;
  }
  return a.floor = (i) => (e(i = /* @__PURE__ */ new Date(+i)), i), a.ceil = (i) => (e(i = new Date(i - 1)), t(i, 1), e(i), i), a.round = (i) => {
    const o = a(i), u = a.ceil(i);
    return i - o < u - i ? o : u;
  }, a.offset = (i, o) => (t(i = /* @__PURE__ */ new Date(+i), o == null ? 1 : Math.floor(o)), i), a.range = (i, o, u) => {
    const l = [];
    if (i = a.ceil(i), u = u == null ? 1 : Math.floor(u), !(i < o) || !(u > 0)) return l;
    let s;
    do
      l.push(s = /* @__PURE__ */ new Date(+i)), t(i, u), e(i);
    while (s < i && i < o);
    return l;
  }, a.filter = (i) => Xe((o) => {
    if (o >= o) for (; e(o), !i(o); ) o.setTime(o - 1);
  }, (o, u) => {
    if (o >= o)
      if (u < 0) for (; ++u <= 0; )
        for (; t(o, -1), !i(o); )
          ;
      else for (; --u >= 0; )
        for (; t(o, 1), !i(o); )
          ;
  }), r && (a.count = (i, o) => (pf.setTime(+i), vf.setTime(+o), e(pf), e(vf), Math.floor(r(pf, vf))), a.every = (i) => (i = Math.floor(i), !isFinite(i) || !(i > 0) ? null : i > 1 ? a.filter(n ? (o) => n(o) % i === 0 : (o) => a.count(0, o) % i === 0) : a)), a;
}
const Do = Xe(() => {
}, (e, t) => {
  e.setTime(+e + t);
}, (e, t) => t - e);
Do.every = (e) => (e = Math.floor(e), !isFinite(e) || !(e > 0) ? null : e > 1 ? Xe((t) => {
  t.setTime(Math.floor(t / e) * e);
}, (t, r) => {
  t.setTime(+t + r * e);
}, (t, r) => (r - t) / e) : Do);
Do.range;
const lr = 1e3, Et = lr * 60, sr = Et * 60, vr = sr * 24, Op = vr * 7, X0 = vr * 30, yf = vr * 365, Qr = Xe((e) => {
  e.setTime(e - e.getMilliseconds());
}, (e, t) => {
  e.setTime(+e + t * lr);
}, (e, t) => (t - e) / lr, (e) => e.getUTCSeconds());
Qr.range;
const _p = Xe((e) => {
  e.setTime(e - e.getMilliseconds() - e.getSeconds() * lr);
}, (e, t) => {
  e.setTime(+e + t * Et);
}, (e, t) => (t - e) / Et, (e) => e.getMinutes());
_p.range;
const Sp = Xe((e) => {
  e.setUTCSeconds(0, 0);
}, (e, t) => {
  e.setTime(+e + t * Et);
}, (e, t) => (t - e) / Et, (e) => e.getUTCMinutes());
Sp.range;
const Pp = Xe((e) => {
  e.setTime(e - e.getMilliseconds() - e.getSeconds() * lr - e.getMinutes() * Et);
}, (e, t) => {
  e.setTime(+e + t * sr);
}, (e, t) => (t - e) / sr, (e) => e.getHours());
Pp.range;
const Ap = Xe((e) => {
  e.setUTCMinutes(0, 0, 0);
}, (e, t) => {
  e.setTime(+e + t * sr);
}, (e, t) => (t - e) / sr, (e) => e.getUTCHours());
Ap.range;
const Li = Xe(
  (e) => e.setHours(0, 0, 0, 0),
  (e, t) => e.setDate(e.getDate() + t),
  (e, t) => (t - e - (t.getTimezoneOffset() - e.getTimezoneOffset()) * Et) / vr,
  (e) => e.getDate() - 1
);
Li.range;
const ju = Xe((e) => {
  e.setUTCHours(0, 0, 0, 0);
}, (e, t) => {
  e.setUTCDate(e.getUTCDate() + t);
}, (e, t) => (t - e) / vr, (e) => e.getUTCDate() - 1);
ju.range;
const NO = Xe((e) => {
  e.setUTCHours(0, 0, 0, 0);
}, (e, t) => {
  e.setUTCDate(e.getUTCDate() + t);
}, (e, t) => (t - e) / vr, (e) => Math.floor(e / vr));
NO.range;
function yn(e) {
  return Xe((t) => {
    t.setDate(t.getDate() - (t.getDay() + 7 - e) % 7), t.setHours(0, 0, 0, 0);
  }, (t, r) => {
    t.setDate(t.getDate() + r * 7);
  }, (t, r) => (r - t - (r.getTimezoneOffset() - t.getTimezoneOffset()) * Et) / Op);
}
const Nu = yn(0), Lo = yn(1), K$ = yn(2), V$ = yn(3), Fn = yn(4), X$ = yn(5), Y$ = yn(6);
Nu.range;
Lo.range;
K$.range;
V$.range;
Fn.range;
X$.range;
Y$.range;
function mn(e) {
  return Xe((t) => {
    t.setUTCDate(t.getUTCDate() - (t.getUTCDay() + 7 - e) % 7), t.setUTCHours(0, 0, 0, 0);
  }, (t, r) => {
    t.setUTCDate(t.getUTCDate() + r * 7);
  }, (t, r) => (r - t) / Op);
}
const Cu = mn(0), qo = mn(1), Z$ = mn(2), J$ = mn(3), zn = mn(4), Q$ = mn(5), eR = mn(6);
Cu.range;
qo.range;
Z$.range;
J$.range;
zn.range;
Q$.range;
eR.range;
const Ep = Xe((e) => {
  e.setDate(1), e.setHours(0, 0, 0, 0);
}, (e, t) => {
  e.setMonth(e.getMonth() + t);
}, (e, t) => t.getMonth() - e.getMonth() + (t.getFullYear() - e.getFullYear()) * 12, (e) => e.getMonth());
Ep.range;
const Tp = Xe((e) => {
  e.setUTCDate(1), e.setUTCHours(0, 0, 0, 0);
}, (e, t) => {
  e.setUTCMonth(e.getUTCMonth() + t);
}, (e, t) => t.getUTCMonth() - e.getUTCMonth() + (t.getUTCFullYear() - e.getUTCFullYear()) * 12, (e) => e.getUTCMonth());
Tp.range;
const yr = Xe((e) => {
  e.setMonth(0, 1), e.setHours(0, 0, 0, 0);
}, (e, t) => {
  e.setFullYear(e.getFullYear() + t);
}, (e, t) => t.getFullYear() - e.getFullYear(), (e) => e.getFullYear());
yr.every = (e) => !isFinite(e = Math.floor(e)) || !(e > 0) ? null : Xe((t) => {
  t.setFullYear(Math.floor(t.getFullYear() / e) * e), t.setMonth(0, 1), t.setHours(0, 0, 0, 0);
}, (t, r) => {
  t.setFullYear(t.getFullYear() + r * e);
});
yr.range;
const mr = Xe((e) => {
  e.setUTCMonth(0, 1), e.setUTCHours(0, 0, 0, 0);
}, (e, t) => {
  e.setUTCFullYear(e.getUTCFullYear() + t);
}, (e, t) => t.getUTCFullYear() - e.getUTCFullYear(), (e) => e.getUTCFullYear());
mr.every = (e) => !isFinite(e = Math.floor(e)) || !(e > 0) ? null : Xe((t) => {
  t.setUTCFullYear(Math.floor(t.getUTCFullYear() / e) * e), t.setUTCMonth(0, 1), t.setUTCHours(0, 0, 0, 0);
}, (t, r) => {
  t.setUTCFullYear(t.getUTCFullYear() + r * e);
});
mr.range;
function CO(e, t, r, n, a, i) {
  const o = [
    [Qr, 1, lr],
    [Qr, 5, 5 * lr],
    [Qr, 15, 15 * lr],
    [Qr, 30, 30 * lr],
    [i, 1, Et],
    [i, 5, 5 * Et],
    [i, 15, 15 * Et],
    [i, 30, 30 * Et],
    [a, 1, sr],
    [a, 3, 3 * sr],
    [a, 6, 6 * sr],
    [a, 12, 12 * sr],
    [n, 1, vr],
    [n, 2, 2 * vr],
    [r, 1, Op],
    [t, 1, X0],
    [t, 3, 3 * X0],
    [e, 1, yf]
  ];
  function u(s, f, c) {
    const d = f < s;
    d && ([s, f] = [f, s]);
    const h = c && typeof c.range == "function" ? c : l(s, f, c), y = h ? h.range(s, +f + 1) : [];
    return d ? y.reverse() : y;
  }
  function l(s, f, c) {
    const d = Math.abs(f - s) / c, h = cp(([, , p]) => p).right(o, d);
    if (h === o.length) return e.every(Cd(s / yf, f / yf, c));
    if (h === 0) return Do.every(Math.max(Cd(s, f, c), 1));
    const [y, v] = o[d / o[h - 1][2] < o[h][2] / d ? h - 1 : h];
    return y.every(v);
  }
  return [u, l];
}
const [tR, rR] = CO(mr, Tp, Cu, NO, Ap, Sp), [nR, aR] = CO(yr, Ep, Nu, Li, Pp, _p);
function mf(e) {
  if (0 <= e.y && e.y < 100) {
    var t = new Date(-1, e.m, e.d, e.H, e.M, e.S, e.L);
    return t.setFullYear(e.y), t;
  }
  return new Date(e.y, e.m, e.d, e.H, e.M, e.S, e.L);
}
function gf(e) {
  if (0 <= e.y && e.y < 100) {
    var t = new Date(Date.UTC(-1, e.m, e.d, e.H, e.M, e.S, e.L));
    return t.setUTCFullYear(e.y), t;
  }
  return new Date(Date.UTC(e.y, e.m, e.d, e.H, e.M, e.S, e.L));
}
function wa(e, t, r) {
  return { y: e, m: t, d: r, H: 0, M: 0, S: 0, L: 0 };
}
function iR(e) {
  var t = e.dateTime, r = e.date, n = e.time, a = e.periods, i = e.days, o = e.shortDays, u = e.months, l = e.shortMonths, s = Oa(a), f = _a(a), c = Oa(i), d = _a(i), h = Oa(o), y = _a(o), v = Oa(u), p = _a(u), g = Oa(l), b = _a(l), w = {
    a: z,
    A: F,
    b: W,
    B: X,
    c: null,
    d: tb,
    e: tb,
    f: TR,
    g: LR,
    G: BR,
    H: PR,
    I: AR,
    j: ER,
    L: $O,
    m: MR,
    M: jR,
    p: J,
    q: G,
    Q: ab,
    s: ib,
    S: NR,
    u: CR,
    U: $R,
    V: RR,
    w: kR,
    W: IR,
    x: null,
    X: null,
    y: DR,
    Y: qR,
    Z: FR,
    "%": nb
  }, _ = {
    a: Q,
    A: de,
    b: ge,
    B: qe,
    c: null,
    d: rb,
    e: rb,
    f: HR,
    g: tk,
    G: nk,
    H: zR,
    I: UR,
    j: WR,
    L: kO,
    m: GR,
    M: KR,
    p: bt,
    q: Fe,
    Q: ab,
    s: ib,
    S: VR,
    u: XR,
    U: YR,
    V: ZR,
    w: JR,
    W: QR,
    x: null,
    X: null,
    y: ek,
    Y: rk,
    Z: ak,
    "%": nb
  }, m = {
    a: C,
    A,
    b: N,
    B: $,
    c: D,
    d: Q0,
    e: Q0,
    f: wR,
    g: J0,
    G: Z0,
    H: eb,
    I: eb,
    j: mR,
    L: xR,
    m: yR,
    M: gR,
    p: T,
    q: vR,
    Q: _R,
    s: SR,
    S: bR,
    u: cR,
    U: fR,
    V: dR,
    w: sR,
    W: hR,
    x: R,
    X: L,
    y: J0,
    Y: Z0,
    Z: pR,
    "%": OR
  };
  w.x = O(r, w), w.X = O(n, w), w.c = O(t, w), _.x = O(r, _), _.X = O(n, _), _.c = O(t, _);
  function O(V, le) {
    return function(ce) {
      var B = [], Ee = -1, ye = 0, Be = V.length, je, rt, zt;
      for (ce instanceof Date || (ce = /* @__PURE__ */ new Date(+ce)); ++Ee < Be; )
        V.charCodeAt(Ee) === 37 && (B.push(V.slice(ye, Ee)), (rt = Y0[je = V.charAt(++Ee)]) != null ? je = V.charAt(++Ee) : rt = je === "e" ? " " : "0", (zt = le[je]) && (je = zt(ce, rt)), B.push(je), ye = Ee + 1);
      return B.push(V.slice(ye, Ee)), B.join("");
    };
  }
  function x(V, le) {
    return function(ce) {
      var B = wa(1900, void 0, 1), Ee = S(B, V, ce += "", 0), ye, Be;
      if (Ee != ce.length) return null;
      if ("Q" in B) return new Date(B.Q);
      if ("s" in B) return new Date(B.s * 1e3 + ("L" in B ? B.L : 0));
      if (le && !("Z" in B) && (B.Z = 0), "p" in B && (B.H = B.H % 12 + B.p * 12), B.m === void 0 && (B.m = "q" in B ? B.q : 0), "V" in B) {
        if (B.V < 1 || B.V > 53) return null;
        "w" in B || (B.w = 1), "Z" in B ? (ye = gf(wa(B.y, 0, 1)), Be = ye.getUTCDay(), ye = Be > 4 || Be === 0 ? qo.ceil(ye) : qo(ye), ye = ju.offset(ye, (B.V - 1) * 7), B.y = ye.getUTCFullYear(), B.m = ye.getUTCMonth(), B.d = ye.getUTCDate() + (B.w + 6) % 7) : (ye = mf(wa(B.y, 0, 1)), Be = ye.getDay(), ye = Be > 4 || Be === 0 ? Lo.ceil(ye) : Lo(ye), ye = Li.offset(ye, (B.V - 1) * 7), B.y = ye.getFullYear(), B.m = ye.getMonth(), B.d = ye.getDate() + (B.w + 6) % 7);
      } else ("W" in B || "U" in B) && ("w" in B || (B.w = "u" in B ? B.u % 7 : "W" in B ? 1 : 0), Be = "Z" in B ? gf(wa(B.y, 0, 1)).getUTCDay() : mf(wa(B.y, 0, 1)).getDay(), B.m = 0, B.d = "W" in B ? (B.w + 6) % 7 + B.W * 7 - (Be + 5) % 7 : B.w + B.U * 7 - (Be + 6) % 7);
      return "Z" in B ? (B.H += B.Z / 100 | 0, B.M += B.Z % 100, gf(B)) : mf(B);
    };
  }
  function S(V, le, ce, B) {
    for (var Ee = 0, ye = le.length, Be = ce.length, je, rt; Ee < ye; ) {
      if (B >= Be) return -1;
      if (je = le.charCodeAt(Ee++), je === 37) {
        if (je = le.charAt(Ee++), rt = m[je in Y0 ? le.charAt(Ee++) : je], !rt || (B = rt(V, ce, B)) < 0) return -1;
      } else if (je != ce.charCodeAt(B++))
        return -1;
    }
    return B;
  }
  function T(V, le, ce) {
    var B = s.exec(le.slice(ce));
    return B ? (V.p = f.get(B[0].toLowerCase()), ce + B[0].length) : -1;
  }
  function C(V, le, ce) {
    var B = h.exec(le.slice(ce));
    return B ? (V.w = y.get(B[0].toLowerCase()), ce + B[0].length) : -1;
  }
  function A(V, le, ce) {
    var B = c.exec(le.slice(ce));
    return B ? (V.w = d.get(B[0].toLowerCase()), ce + B[0].length) : -1;
  }
  function N(V, le, ce) {
    var B = g.exec(le.slice(ce));
    return B ? (V.m = b.get(B[0].toLowerCase()), ce + B[0].length) : -1;
  }
  function $(V, le, ce) {
    var B = v.exec(le.slice(ce));
    return B ? (V.m = p.get(B[0].toLowerCase()), ce + B[0].length) : -1;
  }
  function D(V, le, ce) {
    return S(V, t, le, ce);
  }
  function R(V, le, ce) {
    return S(V, r, le, ce);
  }
  function L(V, le, ce) {
    return S(V, n, le, ce);
  }
  function z(V) {
    return o[V.getDay()];
  }
  function F(V) {
    return i[V.getDay()];
  }
  function W(V) {
    return l[V.getMonth()];
  }
  function X(V) {
    return u[V.getMonth()];
  }
  function J(V) {
    return a[+(V.getHours() >= 12)];
  }
  function G(V) {
    return 1 + ~~(V.getMonth() / 3);
  }
  function Q(V) {
    return o[V.getUTCDay()];
  }
  function de(V) {
    return i[V.getUTCDay()];
  }
  function ge(V) {
    return l[V.getUTCMonth()];
  }
  function qe(V) {
    return u[V.getUTCMonth()];
  }
  function bt(V) {
    return a[+(V.getUTCHours() >= 12)];
  }
  function Fe(V) {
    return 1 + ~~(V.getUTCMonth() / 3);
  }
  return {
    format: function(V) {
      var le = O(V += "", w);
      return le.toString = function() {
        return V;
      }, le;
    },
    parse: function(V) {
      var le = x(V += "", !1);
      return le.toString = function() {
        return V;
      }, le;
    },
    utcFormat: function(V) {
      var le = O(V += "", _);
      return le.toString = function() {
        return V;
      }, le;
    },
    utcParse: function(V) {
      var le = x(V += "", !0);
      return le.toString = function() {
        return V;
      }, le;
    }
  };
}
var Y0 = { "-": "", _: " ", 0: "0" }, Je = /^\s*\d+/, oR = /^%/, uR = /[\\^$*+?|[\]().{}]/g;
function Se(e, t, r) {
  var n = e < 0 ? "-" : "", a = (n ? -e : e) + "", i = a.length;
  return n + (i < r ? new Array(r - i + 1).join(t) + a : a);
}
function lR(e) {
  return e.replace(uR, "\\$&");
}
function Oa(e) {
  return new RegExp("^(?:" + e.map(lR).join("|") + ")", "i");
}
function _a(e) {
  return new Map(e.map((t, r) => [t.toLowerCase(), r]));
}
function sR(e, t, r) {
  var n = Je.exec(t.slice(r, r + 1));
  return n ? (e.w = +n[0], r + n[0].length) : -1;
}
function cR(e, t, r) {
  var n = Je.exec(t.slice(r, r + 1));
  return n ? (e.u = +n[0], r + n[0].length) : -1;
}
function fR(e, t, r) {
  var n = Je.exec(t.slice(r, r + 2));
  return n ? (e.U = +n[0], r + n[0].length) : -1;
}
function dR(e, t, r) {
  var n = Je.exec(t.slice(r, r + 2));
  return n ? (e.V = +n[0], r + n[0].length) : -1;
}
function hR(e, t, r) {
  var n = Je.exec(t.slice(r, r + 2));
  return n ? (e.W = +n[0], r + n[0].length) : -1;
}
function Z0(e, t, r) {
  var n = Je.exec(t.slice(r, r + 4));
  return n ? (e.y = +n[0], r + n[0].length) : -1;
}
function J0(e, t, r) {
  var n = Je.exec(t.slice(r, r + 2));
  return n ? (e.y = +n[0] + (+n[0] > 68 ? 1900 : 2e3), r + n[0].length) : -1;
}
function pR(e, t, r) {
  var n = /^(Z)|([+-]\d\d)(?::?(\d\d))?/.exec(t.slice(r, r + 6));
  return n ? (e.Z = n[1] ? 0 : -(n[2] + (n[3] || "00")), r + n[0].length) : -1;
}
function vR(e, t, r) {
  var n = Je.exec(t.slice(r, r + 1));
  return n ? (e.q = n[0] * 3 - 3, r + n[0].length) : -1;
}
function yR(e, t, r) {
  var n = Je.exec(t.slice(r, r + 2));
  return n ? (e.m = n[0] - 1, r + n[0].length) : -1;
}
function Q0(e, t, r) {
  var n = Je.exec(t.slice(r, r + 2));
  return n ? (e.d = +n[0], r + n[0].length) : -1;
}
function mR(e, t, r) {
  var n = Je.exec(t.slice(r, r + 3));
  return n ? (e.m = 0, e.d = +n[0], r + n[0].length) : -1;
}
function eb(e, t, r) {
  var n = Je.exec(t.slice(r, r + 2));
  return n ? (e.H = +n[0], r + n[0].length) : -1;
}
function gR(e, t, r) {
  var n = Je.exec(t.slice(r, r + 2));
  return n ? (e.M = +n[0], r + n[0].length) : -1;
}
function bR(e, t, r) {
  var n = Je.exec(t.slice(r, r + 2));
  return n ? (e.S = +n[0], r + n[0].length) : -1;
}
function xR(e, t, r) {
  var n = Je.exec(t.slice(r, r + 3));
  return n ? (e.L = +n[0], r + n[0].length) : -1;
}
function wR(e, t, r) {
  var n = Je.exec(t.slice(r, r + 6));
  return n ? (e.L = Math.floor(n[0] / 1e3), r + n[0].length) : -1;
}
function OR(e, t, r) {
  var n = oR.exec(t.slice(r, r + 1));
  return n ? r + n[0].length : -1;
}
function _R(e, t, r) {
  var n = Je.exec(t.slice(r));
  return n ? (e.Q = +n[0], r + n[0].length) : -1;
}
function SR(e, t, r) {
  var n = Je.exec(t.slice(r));
  return n ? (e.s = +n[0], r + n[0].length) : -1;
}
function tb(e, t) {
  return Se(e.getDate(), t, 2);
}
function PR(e, t) {
  return Se(e.getHours(), t, 2);
}
function AR(e, t) {
  return Se(e.getHours() % 12 || 12, t, 2);
}
function ER(e, t) {
  return Se(1 + Li.count(yr(e), e), t, 3);
}
function $O(e, t) {
  return Se(e.getMilliseconds(), t, 3);
}
function TR(e, t) {
  return $O(e, t) + "000";
}
function MR(e, t) {
  return Se(e.getMonth() + 1, t, 2);
}
function jR(e, t) {
  return Se(e.getMinutes(), t, 2);
}
function NR(e, t) {
  return Se(e.getSeconds(), t, 2);
}
function CR(e) {
  var t = e.getDay();
  return t === 0 ? 7 : t;
}
function $R(e, t) {
  return Se(Nu.count(yr(e) - 1, e), t, 2);
}
function RO(e) {
  var t = e.getDay();
  return t >= 4 || t === 0 ? Fn(e) : Fn.ceil(e);
}
function RR(e, t) {
  return e = RO(e), Se(Fn.count(yr(e), e) + (yr(e).getDay() === 4), t, 2);
}
function kR(e) {
  return e.getDay();
}
function IR(e, t) {
  return Se(Lo.count(yr(e) - 1, e), t, 2);
}
function DR(e, t) {
  return Se(e.getFullYear() % 100, t, 2);
}
function LR(e, t) {
  return e = RO(e), Se(e.getFullYear() % 100, t, 2);
}
function qR(e, t) {
  return Se(e.getFullYear() % 1e4, t, 4);
}
function BR(e, t) {
  var r = e.getDay();
  return e = r >= 4 || r === 0 ? Fn(e) : Fn.ceil(e), Se(e.getFullYear() % 1e4, t, 4);
}
function FR(e) {
  var t = e.getTimezoneOffset();
  return (t > 0 ? "-" : (t *= -1, "+")) + Se(t / 60 | 0, "0", 2) + Se(t % 60, "0", 2);
}
function rb(e, t) {
  return Se(e.getUTCDate(), t, 2);
}
function zR(e, t) {
  return Se(e.getUTCHours(), t, 2);
}
function UR(e, t) {
  return Se(e.getUTCHours() % 12 || 12, t, 2);
}
function WR(e, t) {
  return Se(1 + ju.count(mr(e), e), t, 3);
}
function kO(e, t) {
  return Se(e.getUTCMilliseconds(), t, 3);
}
function HR(e, t) {
  return kO(e, t) + "000";
}
function GR(e, t) {
  return Se(e.getUTCMonth() + 1, t, 2);
}
function KR(e, t) {
  return Se(e.getUTCMinutes(), t, 2);
}
function VR(e, t) {
  return Se(e.getUTCSeconds(), t, 2);
}
function XR(e) {
  var t = e.getUTCDay();
  return t === 0 ? 7 : t;
}
function YR(e, t) {
  return Se(Cu.count(mr(e) - 1, e), t, 2);
}
function IO(e) {
  var t = e.getUTCDay();
  return t >= 4 || t === 0 ? zn(e) : zn.ceil(e);
}
function ZR(e, t) {
  return e = IO(e), Se(zn.count(mr(e), e) + (mr(e).getUTCDay() === 4), t, 2);
}
function JR(e) {
  return e.getUTCDay();
}
function QR(e, t) {
  return Se(qo.count(mr(e) - 1, e), t, 2);
}
function ek(e, t) {
  return Se(e.getUTCFullYear() % 100, t, 2);
}
function tk(e, t) {
  return e = IO(e), Se(e.getUTCFullYear() % 100, t, 2);
}
function rk(e, t) {
  return Se(e.getUTCFullYear() % 1e4, t, 4);
}
function nk(e, t) {
  var r = e.getUTCDay();
  return e = r >= 4 || r === 0 ? zn(e) : zn.ceil(e), Se(e.getUTCFullYear() % 1e4, t, 4);
}
function ak() {
  return "+0000";
}
function nb() {
  return "%";
}
function ab(e) {
  return +e;
}
function ib(e) {
  return Math.floor(+e / 1e3);
}
var On, DO, LO;
ik({
  dateTime: "%x, %X",
  date: "%-m/%-d/%Y",
  time: "%-I:%M:%S %p",
  periods: ["AM", "PM"],
  days: ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
  shortDays: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],
  months: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
  shortMonths: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"]
});
function ik(e) {
  return On = iR(e), DO = On.format, On.parse, LO = On.utcFormat, On.utcParse, On;
}
function ok(e) {
  return new Date(e);
}
function uk(e) {
  return e instanceof Date ? +e : +/* @__PURE__ */ new Date(+e);
}
function Mp(e, t, r, n, a, i, o, u, l, s) {
  var f = vp(), c = f.invert, d = f.domain, h = s(".%L"), y = s(":%S"), v = s("%I:%M"), p = s("%I %p"), g = s("%a %d"), b = s("%b %d"), w = s("%B"), _ = s("%Y");
  function m(O) {
    return (l(O) < O ? h : u(O) < O ? y : o(O) < O ? v : i(O) < O ? p : n(O) < O ? a(O) < O ? g : b : r(O) < O ? w : _)(O);
  }
  return f.invert = function(O) {
    return new Date(c(O));
  }, f.domain = function(O) {
    return arguments.length ? d(Array.from(O, uk)) : d().map(ok);
  }, f.ticks = function(O) {
    var x = d();
    return e(x[0], x[x.length - 1], O ?? 10);
  }, f.tickFormat = function(O, x) {
    return x == null ? m : s(x);
  }, f.nice = function(O) {
    var x = d();
    return (!O || typeof O.range != "function") && (O = t(x[0], x[x.length - 1], O ?? 10)), O ? d(SO(x, O)) : f;
  }, f.copy = function() {
    return Di(f, Mp(e, t, r, n, a, i, o, u, l, s));
  }, f;
}
function lk() {
  return Ct.apply(Mp(nR, aR, yr, Ep, Nu, Li, Pp, _p, Qr, DO).domain([new Date(2e3, 0, 1), new Date(2e3, 0, 2)]), arguments);
}
function sk() {
  return Ct.apply(Mp(tR, rR, mr, Tp, Cu, ju, Ap, Sp, Qr, LO).domain([Date.UTC(2e3, 0, 1), Date.UTC(2e3, 0, 2)]), arguments);
}
function $u() {
  var e = 0, t = 1, r, n, a, i, o = ct, u = !1, l;
  function s(c) {
    return c == null || isNaN(c = +c) ? l : o(a === 0 ? 0.5 : (c = (i(c) - r) * a, u ? Math.max(0, Math.min(1, c)) : c));
  }
  s.domain = function(c) {
    return arguments.length ? ([e, t] = c, r = i(e = +e), n = i(t = +t), a = r === n ? 0 : 1 / (n - r), s) : [e, t];
  }, s.clamp = function(c) {
    return arguments.length ? (u = !!c, s) : u;
  }, s.interpolator = function(c) {
    return arguments.length ? (o = c, s) : o;
  };
  function f(c) {
    return function(d) {
      var h, y;
      return arguments.length ? ([h, y] = d, o = c(h, y), s) : [o(0), o(1)];
    };
  }
  return s.range = f(la), s.rangeRound = f(pp), s.unknown = function(c) {
    return arguments.length ? (l = c, s) : l;
  }, function(c) {
    return i = c, r = c(e), n = c(t), a = r === n ? 0 : 1 / (n - r), s;
  };
}
function Fr(e, t) {
  return t.domain(e.domain()).interpolator(e.interpolator()).clamp(e.clamp()).unknown(e.unknown());
}
function qO() {
  var e = Br($u()(ct));
  return e.copy = function() {
    return Fr(e, qO());
  }, Or.apply(e, arguments);
}
function BO() {
  var e = gp($u()).domain([1, 10]);
  return e.copy = function() {
    return Fr(e, BO()).base(e.base());
  }, Or.apply(e, arguments);
}
function FO() {
  var e = bp($u());
  return e.copy = function() {
    return Fr(e, FO()).constant(e.constant());
  }, Or.apply(e, arguments);
}
function jp() {
  var e = xp($u());
  return e.copy = function() {
    return Fr(e, jp()).exponent(e.exponent());
  }, Or.apply(e, arguments);
}
function ck() {
  return jp.apply(null, arguments).exponent(0.5);
}
function zO() {
  var e = [], t = ct;
  function r(n) {
    if (n != null && !isNaN(n = +n)) return t((ki(e, n, 1) - 1) / (e.length - 1));
  }
  return r.domain = function(n) {
    if (!arguments.length) return e.slice();
    e = [];
    for (let a of n) a != null && !isNaN(a = +a) && e.push(a);
    return e.sort(Rr), r;
  }, r.interpolator = function(n) {
    return arguments.length ? (t = n, r) : t;
  }, r.range = function() {
    return e.map((n, a) => t(a / (e.length - 1)));
  }, r.quantiles = function(n) {
    return Array.from({ length: n + 1 }, (a, i) => YC(e, i / n));
  }, r.copy = function() {
    return zO(t).domain(e);
  }, Or.apply(r, arguments);
}
function Ru() {
  var e = 0, t = 0.5, r = 1, n = 1, a, i, o, u, l, s = ct, f, c = !1, d;
  function h(v) {
    return isNaN(v = +v) ? d : (v = 0.5 + ((v = +f(v)) - i) * (n * v < n * i ? u : l), s(c ? Math.max(0, Math.min(1, v)) : v));
  }
  h.domain = function(v) {
    return arguments.length ? ([e, t, r] = v, a = f(e = +e), i = f(t = +t), o = f(r = +r), u = a === i ? 0 : 0.5 / (i - a), l = i === o ? 0 : 0.5 / (o - i), n = i < a ? -1 : 1, h) : [e, t, r];
  }, h.clamp = function(v) {
    return arguments.length ? (c = !!v, h) : c;
  }, h.interpolator = function(v) {
    return arguments.length ? (s = v, h) : s;
  };
  function y(v) {
    return function(p) {
      var g, b, w;
      return arguments.length ? ([g, b, w] = p, s = O$(v, [g, b, w]), h) : [s(0), s(0.5), s(1)];
    };
  }
  return h.range = y(la), h.rangeRound = y(pp), h.unknown = function(v) {
    return arguments.length ? (d = v, h) : d;
  }, function(v) {
    return f = v, a = v(e), i = v(t), o = v(r), u = a === i ? 0 : 0.5 / (i - a), l = i === o ? 0 : 0.5 / (o - i), n = i < a ? -1 : 1, h;
  };
}
function UO() {
  var e = Br(Ru()(ct));
  return e.copy = function() {
    return Fr(e, UO());
  }, Or.apply(e, arguments);
}
function WO() {
  var e = gp(Ru()).domain([0.1, 1, 10]);
  return e.copy = function() {
    return Fr(e, WO()).base(e.base());
  }, Or.apply(e, arguments);
}
function HO() {
  var e = bp(Ru());
  return e.copy = function() {
    return Fr(e, HO()).constant(e.constant());
  }, Or.apply(e, arguments);
}
function Np() {
  var e = xp(Ru());
  return e.copy = function() {
    return Fr(e, Np()).exponent(e.exponent());
  }, Or.apply(e, arguments);
}
function fk() {
  return Np.apply(null, arguments).exponent(0.5);
}
const ob = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  scaleBand: Za,
  scaleDiverging: UO,
  scaleDivergingLog: WO,
  scaleDivergingPow: Np,
  scaleDivergingSqrt: fk,
  scaleDivergingSymlog: HO,
  scaleIdentity: _O,
  scaleImplicit: $d,
  scaleLinear: Io,
  scaleLog: PO,
  scaleOrdinal: fp,
  scalePoint: Ia,
  scalePow: wp,
  scaleQuantile: TO,
  scaleQuantize: MO,
  scaleRadial: EO,
  scaleSequential: qO,
  scaleSequentialLog: BO,
  scaleSequentialPow: jp,
  scaleSequentialQuantile: zO,
  scaleSequentialSqrt: ck,
  scaleSequentialSymlog: FO,
  scaleSqrt: H$,
  scaleSymlog: AO,
  scaleThreshold: jO,
  scaleTime: lk,
  scaleUtc: sk,
  tickFormat: OO
}, Symbol.toStringTag, { value: "Module" }));
var bf, ub;
function GO() {
  if (ub) return bf;
  ub = 1;
  var e = na();
  function t(r, n, a) {
    for (var i = -1, o = r.length; ++i < o; ) {
      var u = r[i], l = n(u);
      if (l != null && (s === void 0 ? l === l && !e(l) : a(l, s)))
        var s = l, f = u;
    }
    return f;
  }
  return bf = t, bf;
}
var xf, lb;
function dk() {
  if (lb) return xf;
  lb = 1;
  function e(t, r) {
    return t > r;
  }
  return xf = e, xf;
}
var wf, sb;
function hk() {
  if (sb) return wf;
  sb = 1;
  var e = GO(), t = dk(), r = oa();
  function n(a) {
    return a && a.length ? e(a, r, t) : void 0;
  }
  return wf = n, wf;
}
var pk = hk();
const Nr = /* @__PURE__ */ $e(pk);
var Of, cb;
function vk() {
  if (cb) return Of;
  cb = 1;
  function e(t, r) {
    return t < r;
  }
  return Of = e, Of;
}
var _f, fb;
function yk() {
  if (fb) return _f;
  fb = 1;
  var e = GO(), t = vk(), r = oa();
  function n(a) {
    return a && a.length ? e(a, r, t) : void 0;
  }
  return _f = n, _f;
}
var mk = yk();
const ku = /* @__PURE__ */ $e(mk);
var Sf, db;
function gk() {
  if (db) return Sf;
  db = 1;
  var e = Kh(), t = qr(), r = eO(), n = ht();
  function a(i, o) {
    var u = n(i) ? e : r;
    return u(i, t(o, 3));
  }
  return Sf = a, Sf;
}
var Pf, hb;
function bk() {
  if (hb) return Pf;
  hb = 1;
  var e = Jw(), t = gk();
  function r(n, a) {
    return e(t(n, a), 1);
  }
  return Pf = r, Pf;
}
var xk = bk();
const wk = /* @__PURE__ */ $e(xk);
var Af, pb;
function Ok() {
  if (pb) return Af;
  pb = 1;
  var e = op();
  function t(r, n) {
    return e(r, n);
  }
  return Af = t, Af;
}
var _k = Ok();
const ri = /* @__PURE__ */ $e(_k);
var sa = 1e9, Sk = {
  // These values must be integers within the stated ranges (inclusive).
  // Most of these values can be changed during run-time using `Decimal.config`.
  // The maximum number of significant digits of the result of a calculation or base conversion.
  // E.g. `Decimal.config({ precision: 20 });`
  precision: 20,
  // 1 to MAX_DIGITS
  // The rounding mode used by default by `toInteger`, `toDecimalPlaces`, `toExponential`,
  // `toFixed`, `toPrecision` and `toSignificantDigits`.
  //
  // ROUND_UP         0 Away from zero.
  // ROUND_DOWN       1 Towards zero.
  // ROUND_CEIL       2 Towards +Infinity.
  // ROUND_FLOOR      3 Towards -Infinity.
  // ROUND_HALF_UP    4 Towards nearest neighbour. If equidistant, up.
  // ROUND_HALF_DOWN  5 Towards nearest neighbour. If equidistant, down.
  // ROUND_HALF_EVEN  6 Towards nearest neighbour. If equidistant, towards even neighbour.
  // ROUND_HALF_CEIL  7 Towards nearest neighbour. If equidistant, towards +Infinity.
  // ROUND_HALF_FLOOR 8 Towards nearest neighbour. If equidistant, towards -Infinity.
  //
  // E.g.
  // `Decimal.rounding = 4;`
  // `Decimal.rounding = Decimal.ROUND_HALF_UP;`
  rounding: 4,
  // 0 to 8
  // The exponent value at and beneath which `toString` returns exponential notation.
  // JavaScript numbers: -7
  toExpNeg: -7,
  // 0 to -MAX_E
  // The exponent value at and above which `toString` returns exponential notation.
  // JavaScript numbers: 21
  toExpPos: 21,
  // 0 to MAX_E
  // The natural logarithm of 10.
  // 115 digits
  LN10: "2.302585092994045684017991454684364207601101488628772976033327900967572609677352480235997205089598298341967784042286"
}, $p, ke = !0, jt = "[DecimalError] ", an = jt + "Invalid argument: ", Cp = jt + "Exponent out of range: ", ca = Math.floor, Xr = Math.pow, Pk = /^(\d+(\.\d*)?|\.\d+)(e[+-]?\d+)?$/i, mt, Ze = 1e7, Re = 7, KO = 9007199254740991, Bo = ca(KO / Re), Y = {};
Y.absoluteValue = Y.abs = function() {
  var e = new this.constructor(this);
  return e.s && (e.s = 1), e;
};
Y.comparedTo = Y.cmp = function(e) {
  var t, r, n, a, i = this;
  if (e = new i.constructor(e), i.s !== e.s) return i.s || -e.s;
  if (i.e !== e.e) return i.e > e.e ^ i.s < 0 ? 1 : -1;
  for (n = i.d.length, a = e.d.length, t = 0, r = n < a ? n : a; t < r; ++t)
    if (i.d[t] !== e.d[t]) return i.d[t] > e.d[t] ^ i.s < 0 ? 1 : -1;
  return n === a ? 0 : n > a ^ i.s < 0 ? 1 : -1;
};
Y.decimalPlaces = Y.dp = function() {
  var e = this, t = e.d.length - 1, r = (t - e.e) * Re;
  if (t = e.d[t], t) for (; t % 10 == 0; t /= 10) r--;
  return r < 0 ? 0 : r;
};
Y.dividedBy = Y.div = function(e) {
  return hr(this, new this.constructor(e));
};
Y.dividedToIntegerBy = Y.idiv = function(e) {
  var t = this, r = t.constructor;
  return Ne(hr(t, new r(e), 0, 1), r.precision);
};
Y.equals = Y.eq = function(e) {
  return !this.cmp(e);
};
Y.exponent = function() {
  return He(this);
};
Y.greaterThan = Y.gt = function(e) {
  return this.cmp(e) > 0;
};
Y.greaterThanOrEqualTo = Y.gte = function(e) {
  return this.cmp(e) >= 0;
};
Y.isInteger = Y.isint = function() {
  return this.e > this.d.length - 2;
};
Y.isNegative = Y.isneg = function() {
  return this.s < 0;
};
Y.isPositive = Y.ispos = function() {
  return this.s > 0;
};
Y.isZero = function() {
  return this.s === 0;
};
Y.lessThan = Y.lt = function(e) {
  return this.cmp(e) < 0;
};
Y.lessThanOrEqualTo = Y.lte = function(e) {
  return this.cmp(e) < 1;
};
Y.logarithm = Y.log = function(e) {
  var t, r = this, n = r.constructor, a = n.precision, i = a + 5;
  if (e === void 0)
    e = new n(10);
  else if (e = new n(e), e.s < 1 || e.eq(mt)) throw Error(jt + "NaN");
  if (r.s < 1) throw Error(jt + (r.s ? "NaN" : "-Infinity"));
  return r.eq(mt) ? new n(0) : (ke = !1, t = hr(ni(r, i), ni(e, i), i), ke = !0, Ne(t, a));
};
Y.minus = Y.sub = function(e) {
  var t = this;
  return e = new t.constructor(e), t.s == e.s ? YO(t, e) : VO(t, (e.s = -e.s, e));
};
Y.modulo = Y.mod = function(e) {
  var t, r = this, n = r.constructor, a = n.precision;
  if (e = new n(e), !e.s) throw Error(jt + "NaN");
  return r.s ? (ke = !1, t = hr(r, e, 0, 1).times(e), ke = !0, r.minus(t)) : Ne(new n(r), a);
};
Y.naturalExponential = Y.exp = function() {
  return XO(this);
};
Y.naturalLogarithm = Y.ln = function() {
  return ni(this);
};
Y.negated = Y.neg = function() {
  var e = new this.constructor(this);
  return e.s = -e.s || 0, e;
};
Y.plus = Y.add = function(e) {
  var t = this;
  return e = new t.constructor(e), t.s == e.s ? VO(t, e) : YO(t, (e.s = -e.s, e));
};
Y.precision = Y.sd = function(e) {
  var t, r, n, a = this;
  if (e !== void 0 && e !== !!e && e !== 1 && e !== 0) throw Error(an + e);
  if (t = He(a) + 1, n = a.d.length - 1, r = n * Re + 1, n = a.d[n], n) {
    for (; n % 10 == 0; n /= 10) r--;
    for (n = a.d[0]; n >= 10; n /= 10) r++;
  }
  return e && t > r ? t : r;
};
Y.squareRoot = Y.sqrt = function() {
  var e, t, r, n, a, i, o, u = this, l = u.constructor;
  if (u.s < 1) {
    if (!u.s) return new l(0);
    throw Error(jt + "NaN");
  }
  for (e = He(u), ke = !1, a = Math.sqrt(+u), a == 0 || a == 1 / 0 ? (t = Ht(u.d), (t.length + e) % 2 == 0 && (t += "0"), a = Math.sqrt(t), e = ca((e + 1) / 2) - (e < 0 || e % 2), a == 1 / 0 ? t = "5e" + e : (t = a.toExponential(), t = t.slice(0, t.indexOf("e") + 1) + e), n = new l(t)) : n = new l(a.toString()), r = l.precision, a = o = r + 3; ; )
    if (i = n, n = i.plus(hr(u, i, o + 2)).times(0.5), Ht(i.d).slice(0, o) === (t = Ht(n.d)).slice(0, o)) {
      if (t = t.slice(o - 3, o + 1), a == o && t == "4999") {
        if (Ne(i, r + 1, 0), i.times(i).eq(u)) {
          n = i;
          break;
        }
      } else if (t != "9999")
        break;
      o += 4;
    }
  return ke = !0, Ne(n, r);
};
Y.times = Y.mul = function(e) {
  var t, r, n, a, i, o, u, l, s, f = this, c = f.constructor, d = f.d, h = (e = new c(e)).d;
  if (!f.s || !e.s) return new c(0);
  for (e.s *= f.s, r = f.e + e.e, l = d.length, s = h.length, l < s && (i = d, d = h, h = i, o = l, l = s, s = o), i = [], o = l + s, n = o; n--; ) i.push(0);
  for (n = s; --n >= 0; ) {
    for (t = 0, a = l + n; a > n; )
      u = i[a] + h[n] * d[a - n - 1] + t, i[a--] = u % Ze | 0, t = u / Ze | 0;
    i[a] = (i[a] + t) % Ze | 0;
  }
  for (; !i[--o]; ) i.pop();
  return t ? ++r : i.shift(), e.d = i, e.e = r, ke ? Ne(e, c.precision) : e;
};
Y.toDecimalPlaces = Y.todp = function(e, t) {
  var r = this, n = r.constructor;
  return r = new n(r), e === void 0 ? r : (Zt(e, 0, sa), t === void 0 ? t = n.rounding : Zt(t, 0, 8), Ne(r, e + He(r) + 1, t));
};
Y.toExponential = function(e, t) {
  var r, n = this, a = n.constructor;
  return e === void 0 ? r = un(n, !0) : (Zt(e, 0, sa), t === void 0 ? t = a.rounding : Zt(t, 0, 8), n = Ne(new a(n), e + 1, t), r = un(n, !0, e + 1)), r;
};
Y.toFixed = function(e, t) {
  var r, n, a = this, i = a.constructor;
  return e === void 0 ? un(a) : (Zt(e, 0, sa), t === void 0 ? t = i.rounding : Zt(t, 0, 8), n = Ne(new i(a), e + He(a) + 1, t), r = un(n.abs(), !1, e + He(n) + 1), a.isneg() && !a.isZero() ? "-" + r : r);
};
Y.toInteger = Y.toint = function() {
  var e = this, t = e.constructor;
  return Ne(new t(e), He(e) + 1, t.rounding);
};
Y.toNumber = function() {
  return +this;
};
Y.toPower = Y.pow = function(e) {
  var t, r, n, a, i, o, u = this, l = u.constructor, s = 12, f = +(e = new l(e));
  if (!e.s) return new l(mt);
  if (u = new l(u), !u.s) {
    if (e.s < 1) throw Error(jt + "Infinity");
    return u;
  }
  if (u.eq(mt)) return u;
  if (n = l.precision, e.eq(mt)) return Ne(u, n);
  if (t = e.e, r = e.d.length - 1, o = t >= r, i = u.s, o) {
    if ((r = f < 0 ? -f : f) <= KO) {
      for (a = new l(mt), t = Math.ceil(n / Re + 4), ke = !1; r % 2 && (a = a.times(u), yb(a.d, t)), r = ca(r / 2), r !== 0; )
        u = u.times(u), yb(u.d, t);
      return ke = !0, e.s < 0 ? new l(mt).div(a) : Ne(a, n);
    }
  } else if (i < 0) throw Error(jt + "NaN");
  return i = i < 0 && e.d[Math.max(t, r)] & 1 ? -1 : 1, u.s = 1, ke = !1, a = e.times(ni(u, n + s)), ke = !0, a = XO(a), a.s = i, a;
};
Y.toPrecision = function(e, t) {
  var r, n, a = this, i = a.constructor;
  return e === void 0 ? (r = He(a), n = un(a, r <= i.toExpNeg || r >= i.toExpPos)) : (Zt(e, 1, sa), t === void 0 ? t = i.rounding : Zt(t, 0, 8), a = Ne(new i(a), e, t), r = He(a), n = un(a, e <= r || r <= i.toExpNeg, e)), n;
};
Y.toSignificantDigits = Y.tosd = function(e, t) {
  var r = this, n = r.constructor;
  return e === void 0 ? (e = n.precision, t = n.rounding) : (Zt(e, 1, sa), t === void 0 ? t = n.rounding : Zt(t, 0, 8)), Ne(new n(r), e, t);
};
Y.toString = Y.valueOf = Y.val = Y.toJSON = Y[Symbol.for("nodejs.util.inspect.custom")] = function() {
  var e = this, t = He(e), r = e.constructor;
  return un(e, t <= r.toExpNeg || t >= r.toExpPos);
};
function VO(e, t) {
  var r, n, a, i, o, u, l, s, f = e.constructor, c = f.precision;
  if (!e.s || !t.s)
    return t.s || (t = new f(e)), ke ? Ne(t, c) : t;
  if (l = e.d, s = t.d, o = e.e, a = t.e, l = l.slice(), i = o - a, i) {
    for (i < 0 ? (n = l, i = -i, u = s.length) : (n = s, a = o, u = l.length), o = Math.ceil(c / Re), u = o > u ? o + 1 : u + 1, i > u && (i = u, n.length = 1), n.reverse(); i--; ) n.push(0);
    n.reverse();
  }
  for (u = l.length, i = s.length, u - i < 0 && (i = u, n = s, s = l, l = n), r = 0; i; )
    r = (l[--i] = l[i] + s[i] + r) / Ze | 0, l[i] %= Ze;
  for (r && (l.unshift(r), ++a), u = l.length; l[--u] == 0; ) l.pop();
  return t.d = l, t.e = a, ke ? Ne(t, c) : t;
}
function Zt(e, t, r) {
  if (e !== ~~e || e < t || e > r)
    throw Error(an + e);
}
function Ht(e) {
  var t, r, n, a = e.length - 1, i = "", o = e[0];
  if (a > 0) {
    for (i += o, t = 1; t < a; t++)
      n = e[t] + "", r = Re - n.length, r && (i += Ar(r)), i += n;
    o = e[t], n = o + "", r = Re - n.length, r && (i += Ar(r));
  } else if (o === 0)
    return "0";
  for (; o % 10 === 0; ) o /= 10;
  return i + o;
}
var hr = /* @__PURE__ */ (function() {
  function e(n, a) {
    var i, o = 0, u = n.length;
    for (n = n.slice(); u--; )
      i = n[u] * a + o, n[u] = i % Ze | 0, o = i / Ze | 0;
    return o && n.unshift(o), n;
  }
  function t(n, a, i, o) {
    var u, l;
    if (i != o)
      l = i > o ? 1 : -1;
    else
      for (u = l = 0; u < i; u++)
        if (n[u] != a[u]) {
          l = n[u] > a[u] ? 1 : -1;
          break;
        }
    return l;
  }
  function r(n, a, i) {
    for (var o = 0; i--; )
      n[i] -= o, o = n[i] < a[i] ? 1 : 0, n[i] = o * Ze + n[i] - a[i];
    for (; !n[0] && n.length > 1; ) n.shift();
  }
  return function(n, a, i, o) {
    var u, l, s, f, c, d, h, y, v, p, g, b, w, _, m, O, x, S, T = n.constructor, C = n.s == a.s ? 1 : -1, A = n.d, N = a.d;
    if (!n.s) return new T(n);
    if (!a.s) throw Error(jt + "Division by zero");
    for (l = n.e - a.e, x = N.length, m = A.length, h = new T(C), y = h.d = [], s = 0; N[s] == (A[s] || 0); ) ++s;
    if (N[s] > (A[s] || 0) && --l, i == null ? b = i = T.precision : o ? b = i + (He(n) - He(a)) + 1 : b = i, b < 0) return new T(0);
    if (b = b / Re + 2 | 0, s = 0, x == 1)
      for (f = 0, N = N[0], b++; (s < m || f) && b--; s++)
        w = f * Ze + (A[s] || 0), y[s] = w / N | 0, f = w % N | 0;
    else {
      for (f = Ze / (N[0] + 1) | 0, f > 1 && (N = e(N, f), A = e(A, f), x = N.length, m = A.length), _ = x, v = A.slice(0, x), p = v.length; p < x; ) v[p++] = 0;
      S = N.slice(), S.unshift(0), O = N[0], N[1] >= Ze / 2 && ++O;
      do
        f = 0, u = t(N, v, x, p), u < 0 ? (g = v[0], x != p && (g = g * Ze + (v[1] || 0)), f = g / O | 0, f > 1 ? (f >= Ze && (f = Ze - 1), c = e(N, f), d = c.length, p = v.length, u = t(c, v, d, p), u == 1 && (f--, r(c, x < d ? S : N, d))) : (f == 0 && (u = f = 1), c = N.slice()), d = c.length, d < p && c.unshift(0), r(v, c, p), u == -1 && (p = v.length, u = t(N, v, x, p), u < 1 && (f++, r(v, x < p ? S : N, p))), p = v.length) : u === 0 && (f++, v = [0]), y[s++] = f, u && v[0] ? v[p++] = A[_] || 0 : (v = [A[_]], p = 1);
      while ((_++ < m || v[0] !== void 0) && b--);
    }
    return y[0] || y.shift(), h.e = l, Ne(h, o ? i + He(h) + 1 : i);
  };
})();
function XO(e, t) {
  var r, n, a, i, o, u, l = 0, s = 0, f = e.constructor, c = f.precision;
  if (He(e) > 16) throw Error(Cp + He(e));
  if (!e.s) return new f(mt);
  for (ke = !1, u = c, o = new f(0.03125); e.abs().gte(0.1); )
    e = e.times(o), s += 5;
  for (n = Math.log(Xr(2, s)) / Math.LN10 * 2 + 5 | 0, u += n, r = a = i = new f(mt), f.precision = u; ; ) {
    if (a = Ne(a.times(e), u), r = r.times(++l), o = i.plus(hr(a, r, u)), Ht(o.d).slice(0, u) === Ht(i.d).slice(0, u)) {
      for (; s--; ) i = Ne(i.times(i), u);
      return f.precision = c, t == null ? (ke = !0, Ne(i, c)) : i;
    }
    i = o;
  }
}
function He(e) {
  for (var t = e.e * Re, r = e.d[0]; r >= 10; r /= 10) t++;
  return t;
}
function Ef(e, t, r) {
  if (t > e.LN10.sd())
    throw ke = !0, r && (e.precision = r), Error(jt + "LN10 precision limit exceeded");
  return Ne(new e(e.LN10), t);
}
function Ar(e) {
  for (var t = ""; e--; ) t += "0";
  return t;
}
function ni(e, t) {
  var r, n, a, i, o, u, l, s, f, c = 1, d = 10, h = e, y = h.d, v = h.constructor, p = v.precision;
  if (h.s < 1) throw Error(jt + (h.s ? "NaN" : "-Infinity"));
  if (h.eq(mt)) return new v(0);
  if (t == null ? (ke = !1, s = p) : s = t, h.eq(10))
    return t == null && (ke = !0), Ef(v, s);
  if (s += d, v.precision = s, r = Ht(y), n = r.charAt(0), i = He(h), Math.abs(i) < 15e14) {
    for (; n < 7 && n != 1 || n == 1 && r.charAt(1) > 3; )
      h = h.times(e), r = Ht(h.d), n = r.charAt(0), c++;
    i = He(h), n > 1 ? (h = new v("0." + r), i++) : h = new v(n + "." + r.slice(1));
  } else
    return l = Ef(v, s + 2, p).times(i + ""), h = ni(new v(n + "." + r.slice(1)), s - d).plus(l), v.precision = p, t == null ? (ke = !0, Ne(h, p)) : h;
  for (u = o = h = hr(h.minus(mt), h.plus(mt), s), f = Ne(h.times(h), s), a = 3; ; ) {
    if (o = Ne(o.times(f), s), l = u.plus(hr(o, new v(a), s)), Ht(l.d).slice(0, s) === Ht(u.d).slice(0, s))
      return u = u.times(2), i !== 0 && (u = u.plus(Ef(v, s + 2, p).times(i + ""))), u = hr(u, new v(c), s), v.precision = p, t == null ? (ke = !0, Ne(u, p)) : u;
    u = l, a += 2;
  }
}
function vb(e, t) {
  var r, n, a;
  for ((r = t.indexOf(".")) > -1 && (t = t.replace(".", "")), (n = t.search(/e/i)) > 0 ? (r < 0 && (r = n), r += +t.slice(n + 1), t = t.substring(0, n)) : r < 0 && (r = t.length), n = 0; t.charCodeAt(n) === 48; ) ++n;
  for (a = t.length; t.charCodeAt(a - 1) === 48; ) --a;
  if (t = t.slice(n, a), t) {
    if (a -= n, r = r - n - 1, e.e = ca(r / Re), e.d = [], n = (r + 1) % Re, r < 0 && (n += Re), n < a) {
      for (n && e.d.push(+t.slice(0, n)), a -= Re; n < a; ) e.d.push(+t.slice(n, n += Re));
      t = t.slice(n), n = Re - t.length;
    } else
      n -= a;
    for (; n--; ) t += "0";
    if (e.d.push(+t), ke && (e.e > Bo || e.e < -Bo)) throw Error(Cp + r);
  } else
    e.s = 0, e.e = 0, e.d = [0];
  return e;
}
function Ne(e, t, r) {
  var n, a, i, o, u, l, s, f, c = e.d;
  for (o = 1, i = c[0]; i >= 10; i /= 10) o++;
  if (n = t - o, n < 0)
    n += Re, a = t, s = c[f = 0];
  else {
    if (f = Math.ceil((n + 1) / Re), i = c.length, f >= i) return e;
    for (s = i = c[f], o = 1; i >= 10; i /= 10) o++;
    n %= Re, a = n - Re + o;
  }
  if (r !== void 0 && (i = Xr(10, o - a - 1), u = s / i % 10 | 0, l = t < 0 || c[f + 1] !== void 0 || s % i, l = r < 4 ? (u || l) && (r == 0 || r == (e.s < 0 ? 3 : 2)) : u > 5 || u == 5 && (r == 4 || l || r == 6 && // Check whether the digit to the left of the rounding digit is odd.
  (n > 0 ? a > 0 ? s / Xr(10, o - a) : 0 : c[f - 1]) % 10 & 1 || r == (e.s < 0 ? 8 : 7))), t < 1 || !c[0])
    return l ? (i = He(e), c.length = 1, t = t - i - 1, c[0] = Xr(10, (Re - t % Re) % Re), e.e = ca(-t / Re) || 0) : (c.length = 1, c[0] = e.e = e.s = 0), e;
  if (n == 0 ? (c.length = f, i = 1, f--) : (c.length = f + 1, i = Xr(10, Re - n), c[f] = a > 0 ? (s / Xr(10, o - a) % Xr(10, a) | 0) * i : 0), l)
    for (; ; )
      if (f == 0) {
        (c[0] += i) == Ze && (c[0] = 1, ++e.e);
        break;
      } else {
        if (c[f] += i, c[f] != Ze) break;
        c[f--] = 0, i = 1;
      }
  for (n = c.length; c[--n] === 0; ) c.pop();
  if (ke && (e.e > Bo || e.e < -Bo))
    throw Error(Cp + He(e));
  return e;
}
function YO(e, t) {
  var r, n, a, i, o, u, l, s, f, c, d = e.constructor, h = d.precision;
  if (!e.s || !t.s)
    return t.s ? t.s = -t.s : t = new d(e), ke ? Ne(t, h) : t;
  if (l = e.d, c = t.d, n = t.e, s = e.e, l = l.slice(), o = s - n, o) {
    for (f = o < 0, f ? (r = l, o = -o, u = c.length) : (r = c, n = s, u = l.length), a = Math.max(Math.ceil(h / Re), u) + 2, o > a && (o = a, r.length = 1), r.reverse(), a = o; a--; ) r.push(0);
    r.reverse();
  } else {
    for (a = l.length, u = c.length, f = a < u, f && (u = a), a = 0; a < u; a++)
      if (l[a] != c[a]) {
        f = l[a] < c[a];
        break;
      }
    o = 0;
  }
  for (f && (r = l, l = c, c = r, t.s = -t.s), u = l.length, a = c.length - u; a > 0; --a) l[u++] = 0;
  for (a = c.length; a > o; ) {
    if (l[--a] < c[a]) {
      for (i = a; i && l[--i] === 0; ) l[i] = Ze - 1;
      --l[i], l[a] += Ze;
    }
    l[a] -= c[a];
  }
  for (; l[--u] === 0; ) l.pop();
  for (; l[0] === 0; l.shift()) --n;
  return l[0] ? (t.d = l, t.e = n, ke ? Ne(t, h) : t) : new d(0);
}
function un(e, t, r) {
  var n, a = He(e), i = Ht(e.d), o = i.length;
  return t ? (r && (n = r - o) > 0 ? i = i.charAt(0) + "." + i.slice(1) + Ar(n) : o > 1 && (i = i.charAt(0) + "." + i.slice(1)), i = i + (a < 0 ? "e" : "e+") + a) : a < 0 ? (i = "0." + Ar(-a - 1) + i, r && (n = r - o) > 0 && (i += Ar(n))) : a >= o ? (i += Ar(a + 1 - o), r && (n = r - a - 1) > 0 && (i = i + "." + Ar(n))) : ((n = a + 1) < o && (i = i.slice(0, n) + "." + i.slice(n)), r && (n = r - o) > 0 && (a + 1 === o && (i += "."), i += Ar(n))), e.s < 0 ? "-" + i : i;
}
function yb(e, t) {
  if (e.length > t)
    return e.length = t, !0;
}
function ZO(e) {
  var t, r, n;
  function a(i) {
    var o = this;
    if (!(o instanceof a)) return new a(i);
    if (o.constructor = a, i instanceof a) {
      o.s = i.s, o.e = i.e, o.d = (i = i.d) ? i.slice() : i;
      return;
    }
    if (typeof i == "number") {
      if (i * 0 !== 0)
        throw Error(an + i);
      if (i > 0)
        o.s = 1;
      else if (i < 0)
        i = -i, o.s = -1;
      else {
        o.s = 0, o.e = 0, o.d = [0];
        return;
      }
      if (i === ~~i && i < 1e7) {
        o.e = 0, o.d = [i];
        return;
      }
      return vb(o, i.toString());
    } else if (typeof i != "string")
      throw Error(an + i);
    if (i.charCodeAt(0) === 45 ? (i = i.slice(1), o.s = -1) : o.s = 1, Pk.test(i)) vb(o, i);
    else throw Error(an + i);
  }
  if (a.prototype = Y, a.ROUND_UP = 0, a.ROUND_DOWN = 1, a.ROUND_CEIL = 2, a.ROUND_FLOOR = 3, a.ROUND_HALF_UP = 4, a.ROUND_HALF_DOWN = 5, a.ROUND_HALF_EVEN = 6, a.ROUND_HALF_CEIL = 7, a.ROUND_HALF_FLOOR = 8, a.clone = ZO, a.config = a.set = Ak, e === void 0 && (e = {}), e)
    for (n = ["precision", "rounding", "toExpNeg", "toExpPos", "LN10"], t = 0; t < n.length; ) e.hasOwnProperty(r = n[t++]) || (e[r] = this[r]);
  return a.config(e), a;
}
function Ak(e) {
  if (!e || typeof e != "object")
    throw Error(jt + "Object expected");
  var t, r, n, a = [
    "precision",
    1,
    sa,
    "rounding",
    0,
    8,
    "toExpNeg",
    -1 / 0,
    0,
    "toExpPos",
    0,
    1 / 0
  ];
  for (t = 0; t < a.length; t += 3)
    if ((n = e[r = a[t]]) !== void 0)
      if (ca(n) === n && n >= a[t + 1] && n <= a[t + 2]) this[r] = n;
      else throw Error(an + r + ": " + n);
  if ((n = e[r = "LN10"]) !== void 0)
    if (n == Math.LN10) this[r] = new this(n);
    else throw Error(an + r + ": " + n);
  return this;
}
var $p = ZO(Sk);
mt = new $p(1);
const Me = $p;
function Ek(e) {
  return Nk(e) || jk(e) || Mk(e) || Tk();
}
function Tk() {
  throw new TypeError(`Invalid attempt to spread non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`);
}
function Mk(e, t) {
  if (e) {
    if (typeof e == "string") return Dd(e, t);
    var r = Object.prototype.toString.call(e).slice(8, -1);
    if (r === "Object" && e.constructor && (r = e.constructor.name), r === "Map" || r === "Set") return Array.from(e);
    if (r === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r)) return Dd(e, t);
  }
}
function jk(e) {
  if (typeof Symbol < "u" && Symbol.iterator in Object(e)) return Array.from(e);
}
function Nk(e) {
  if (Array.isArray(e)) return Dd(e);
}
function Dd(e, t) {
  (t == null || t > e.length) && (t = e.length);
  for (var r = 0, n = new Array(t); r < t; r++)
    n[r] = e[r];
  return n;
}
var Ck = function(t) {
  return t;
}, JO = {}, QO = function(t) {
  return t === JO;
}, mb = function(t) {
  return function r() {
    return arguments.length === 0 || arguments.length === 1 && QO(arguments.length <= 0 ? void 0 : arguments[0]) ? r : t.apply(void 0, arguments);
  };
}, $k = function e(t, r) {
  return t === 1 ? r : mb(function() {
    for (var n = arguments.length, a = new Array(n), i = 0; i < n; i++)
      a[i] = arguments[i];
    var o = a.filter(function(u) {
      return u !== JO;
    }).length;
    return o >= t ? r.apply(void 0, a) : e(t - o, mb(function() {
      for (var u = arguments.length, l = new Array(u), s = 0; s < u; s++)
        l[s] = arguments[s];
      var f = a.map(function(c) {
        return QO(c) ? l.shift() : c;
      });
      return r.apply(void 0, Ek(f).concat(l));
    }));
  });
}, Iu = function(t) {
  return $k(t.length, t);
}, Ld = function(t, r) {
  for (var n = [], a = t; a < r; ++a)
    n[a - t] = a;
  return n;
}, Rk = Iu(function(e, t) {
  return Array.isArray(t) ? t.map(e) : Object.keys(t).map(function(r) {
    return t[r];
  }).map(e);
}), kk = function() {
  for (var t = arguments.length, r = new Array(t), n = 0; n < t; n++)
    r[n] = arguments[n];
  if (!r.length)
    return Ck;
  var a = r.reverse(), i = a[0], o = a.slice(1);
  return function() {
    return o.reduce(function(u, l) {
      return l(u);
    }, i.apply(void 0, arguments));
  };
}, qd = function(t) {
  return Array.isArray(t) ? t.reverse() : t.split("").reverse.join("");
}, e_ = function(t) {
  var r = null, n = null;
  return function() {
    for (var a = arguments.length, i = new Array(a), o = 0; o < a; o++)
      i[o] = arguments[o];
    return r && i.every(function(u, l) {
      return u === r[l];
    }) || (r = i, n = t.apply(void 0, i)), n;
  };
};
function Ik(e) {
  var t;
  return e === 0 ? t = 1 : t = Math.floor(new Me(e).abs().log(10).toNumber()) + 1, t;
}
function Dk(e, t, r) {
  for (var n = new Me(e), a = 0, i = []; n.lt(t) && a < 1e5; )
    i.push(n.toNumber()), n = n.add(r), a++;
  return i;
}
var Lk = Iu(function(e, t, r) {
  var n = +e, a = +t;
  return n + r * (a - n);
}), qk = Iu(function(e, t, r) {
  var n = t - +e;
  return n = n || 1 / 0, (r - e) / n;
}), Bk = Iu(function(e, t, r) {
  var n = t - +e;
  return n = n || 1 / 0, Math.max(0, Math.min(1, (r - e) / n));
});
const Du = {
  rangeStep: Dk,
  getDigitCount: Ik,
  interpolateNumber: Lk,
  uninterpolateNumber: qk,
  uninterpolateTruncation: Bk
};
function Bd(e) {
  return Uk(e) || zk(e) || t_(e) || Fk();
}
function Fk() {
  throw new TypeError(`Invalid attempt to spread non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`);
}
function zk(e) {
  if (typeof Symbol < "u" && Symbol.iterator in Object(e)) return Array.from(e);
}
function Uk(e) {
  if (Array.isArray(e)) return Fd(e);
}
function ai(e, t) {
  return Gk(e) || Hk(e, t) || t_(e, t) || Wk();
}
function Wk() {
  throw new TypeError(`Invalid attempt to destructure non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`);
}
function t_(e, t) {
  if (e) {
    if (typeof e == "string") return Fd(e, t);
    var r = Object.prototype.toString.call(e).slice(8, -1);
    if (r === "Object" && e.constructor && (r = e.constructor.name), r === "Map" || r === "Set") return Array.from(e);
    if (r === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r)) return Fd(e, t);
  }
}
function Fd(e, t) {
  (t == null || t > e.length) && (t = e.length);
  for (var r = 0, n = new Array(t); r < t; r++)
    n[r] = e[r];
  return n;
}
function Hk(e, t) {
  if (!(typeof Symbol > "u" || !(Symbol.iterator in Object(e)))) {
    var r = [], n = !0, a = !1, i = void 0;
    try {
      for (var o = e[Symbol.iterator](), u; !(n = (u = o.next()).done) && (r.push(u.value), !(t && r.length === t)); n = !0)
        ;
    } catch (l) {
      a = !0, i = l;
    } finally {
      try {
        !n && o.return != null && o.return();
      } finally {
        if (a) throw i;
      }
    }
    return r;
  }
}
function Gk(e) {
  if (Array.isArray(e)) return e;
}
function r_(e) {
  var t = ai(e, 2), r = t[0], n = t[1], a = r, i = n;
  return r > n && (a = n, i = r), [a, i];
}
function n_(e, t, r) {
  if (e.lte(0))
    return new Me(0);
  var n = Du.getDigitCount(e.toNumber()), a = new Me(10).pow(n), i = e.div(a), o = n !== 1 ? 0.05 : 0.1, u = new Me(Math.ceil(i.div(o).toNumber())).add(r).mul(o), l = u.mul(a);
  return t ? l : new Me(Math.ceil(l));
}
function Kk(e, t, r) {
  var n = 1, a = new Me(e);
  if (!a.isint() && r) {
    var i = Math.abs(e);
    i < 1 ? (n = new Me(10).pow(Du.getDigitCount(e) - 1), a = new Me(Math.floor(a.div(n).toNumber())).mul(n)) : i > 1 && (a = new Me(Math.floor(e)));
  } else e === 0 ? a = new Me(Math.floor((t - 1) / 2)) : r || (a = new Me(Math.floor(e)));
  var o = Math.floor((t - 1) / 2), u = kk(Rk(function(l) {
    return a.add(new Me(l - o).mul(n)).toNumber();
  }), Ld);
  return u(0, t);
}
function a_(e, t, r, n) {
  var a = arguments.length > 4 && arguments[4] !== void 0 ? arguments[4] : 0;
  if (!Number.isFinite((t - e) / (r - 1)))
    return {
      step: new Me(0),
      tickMin: new Me(0),
      tickMax: new Me(0)
    };
  var i = n_(new Me(t).sub(e).div(r - 1), n, a), o;
  e <= 0 && t >= 0 ? o = new Me(0) : (o = new Me(e).add(t).div(2), o = o.sub(new Me(o).mod(i)));
  var u = Math.ceil(o.sub(e).div(i).toNumber()), l = Math.ceil(new Me(t).sub(o).div(i).toNumber()), s = u + l + 1;
  return s > r ? a_(e, t, r, n, a + 1) : (s < r && (l = t > 0 ? l + (r - s) : l, u = t > 0 ? u : u + (r - s)), {
    step: i,
    tickMin: o.sub(new Me(u).mul(i)),
    tickMax: o.add(new Me(l).mul(i))
  });
}
function Vk(e) {
  var t = ai(e, 2), r = t[0], n = t[1], a = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : 6, i = arguments.length > 2 && arguments[2] !== void 0 ? arguments[2] : !0, o = Math.max(a, 2), u = r_([r, n]), l = ai(u, 2), s = l[0], f = l[1];
  if (s === -1 / 0 || f === 1 / 0) {
    var c = f === 1 / 0 ? [s].concat(Bd(Ld(0, a - 1).map(function() {
      return 1 / 0;
    }))) : [].concat(Bd(Ld(0, a - 1).map(function() {
      return -1 / 0;
    })), [f]);
    return r > n ? qd(c) : c;
  }
  if (s === f)
    return Kk(s, a, i);
  var d = a_(s, f, o, i), h = d.step, y = d.tickMin, v = d.tickMax, p = Du.rangeStep(y, v.add(new Me(0.1).mul(h)), h);
  return r > n ? qd(p) : p;
}
function Xk(e, t) {
  var r = ai(e, 2), n = r[0], a = r[1], i = arguments.length > 2 && arguments[2] !== void 0 ? arguments[2] : !0, o = r_([n, a]), u = ai(o, 2), l = u[0], s = u[1];
  if (l === -1 / 0 || s === 1 / 0)
    return [n, a];
  if (l === s)
    return [l];
  var f = Math.max(t, 2), c = n_(new Me(s).sub(l).div(f - 1), i, 0), d = [].concat(Bd(Du.rangeStep(new Me(l), new Me(s).sub(new Me(0.99).mul(c)), c)), [s]);
  return n > a ? qd(d) : d;
}
var Yk = e_(Vk), Zk = e_(Xk), Jk = "Invariant failed";
function ln(e, t) {
  throw new Error(Jk);
}
var Qk = ["offset", "layout", "width", "dataKey", "data", "dataPointFormatter", "xAxis", "yAxis"];
function Un(e) {
  "@babel/helpers - typeof";
  return Un = typeof Symbol == "function" && typeof Symbol.iterator == "symbol" ? function(t) {
    return typeof t;
  } : function(t) {
    return t && typeof Symbol == "function" && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t;
  }, Un(e);
}
function Fo() {
  return Fo = Object.assign ? Object.assign.bind() : function(e) {
    for (var t = 1; t < arguments.length; t++) {
      var r = arguments[t];
      for (var n in r)
        Object.prototype.hasOwnProperty.call(r, n) && (e[n] = r[n]);
    }
    return e;
  }, Fo.apply(this, arguments);
}
function eI(e, t) {
  return aI(e) || nI(e, t) || rI(e, t) || tI();
}
function tI() {
  throw new TypeError(`Invalid attempt to destructure non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`);
}
function rI(e, t) {
  if (e) {
    if (typeof e == "string") return gb(e, t);
    var r = Object.prototype.toString.call(e).slice(8, -1);
    if (r === "Object" && e.constructor && (r = e.constructor.name), r === "Map" || r === "Set") return Array.from(e);
    if (r === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r)) return gb(e, t);
  }
}
function gb(e, t) {
  (t == null || t > e.length) && (t = e.length);
  for (var r = 0, n = new Array(t); r < t; r++) n[r] = e[r];
  return n;
}
function nI(e, t) {
  var r = e == null ? null : typeof Symbol < "u" && e[Symbol.iterator] || e["@@iterator"];
  if (r != null) {
    var n, a, i, o, u = [], l = !0, s = !1;
    try {
      if (i = (r = r.call(e)).next, t !== 0) for (; !(l = (n = i.call(r)).done) && (u.push(n.value), u.length !== t); l = !0) ;
    } catch (f) {
      s = !0, a = f;
    } finally {
      try {
        if (!l && r.return != null && (o = r.return(), Object(o) !== o)) return;
      } finally {
        if (s) throw a;
      }
    }
    return u;
  }
}
function aI(e) {
  if (Array.isArray(e)) return e;
}
function iI(e, t) {
  if (e == null) return {};
  var r = oI(e, t), n, a;
  if (Object.getOwnPropertySymbols) {
    var i = Object.getOwnPropertySymbols(e);
    for (a = 0; a < i.length; a++)
      n = i[a], !(t.indexOf(n) >= 0) && Object.prototype.propertyIsEnumerable.call(e, n) && (r[n] = e[n]);
  }
  return r;
}
function oI(e, t) {
  if (e == null) return {};
  var r = {};
  for (var n in e)
    if (Object.prototype.hasOwnProperty.call(e, n)) {
      if (t.indexOf(n) >= 0) continue;
      r[n] = e[n];
    }
  return r;
}
function uI(e, t) {
  if (!(e instanceof t))
    throw new TypeError("Cannot call a class as a function");
}
function lI(e, t) {
  for (var r = 0; r < t.length; r++) {
    var n = t[r];
    n.enumerable = n.enumerable || !1, n.configurable = !0, "value" in n && (n.writable = !0), Object.defineProperty(e, u_(n.key), n);
  }
}
function sI(e, t, r) {
  return t && lI(e.prototype, t), Object.defineProperty(e, "prototype", { writable: !1 }), e;
}
function cI(e, t, r) {
  return t = zo(t), fI(e, i_() ? Reflect.construct(t, r || [], zo(e).constructor) : t.apply(e, r));
}
function fI(e, t) {
  if (t && (Un(t) === "object" || typeof t == "function"))
    return t;
  if (t !== void 0)
    throw new TypeError("Derived constructors may only return object or undefined");
  return dI(e);
}
function dI(e) {
  if (e === void 0)
    throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
  return e;
}
function i_() {
  try {
    var e = !Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function() {
    }));
  } catch {
  }
  return (i_ = function() {
    return !!e;
  })();
}
function zo(e) {
  return zo = Object.setPrototypeOf ? Object.getPrototypeOf.bind() : function(r) {
    return r.__proto__ || Object.getPrototypeOf(r);
  }, zo(e);
}
function hI(e, t) {
  if (typeof t != "function" && t !== null)
    throw new TypeError("Super expression must either be null or a function");
  e.prototype = Object.create(t && t.prototype, { constructor: { value: e, writable: !0, configurable: !0 } }), Object.defineProperty(e, "prototype", { writable: !1 }), t && zd(e, t);
}
function zd(e, t) {
  return zd = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function(n, a) {
    return n.__proto__ = a, n;
  }, zd(e, t);
}
function o_(e, t, r) {
  return t = u_(t), t in e ? Object.defineProperty(e, t, { value: r, enumerable: !0, configurable: !0, writable: !0 }) : e[t] = r, e;
}
function u_(e) {
  var t = pI(e, "string");
  return Un(t) == "symbol" ? t : t + "";
}
function pI(e, t) {
  if (Un(e) != "object" || !e) return e;
  var r = e[Symbol.toPrimitive];
  if (r !== void 0) {
    var n = r.call(e, t);
    if (Un(n) != "object") return n;
    throw new TypeError("@@toPrimitive must return a primitive value.");
  }
  return String(e);
}
var Lu = /* @__PURE__ */ (function(e) {
  function t() {
    return uI(this, t), cI(this, t, arguments);
  }
  return hI(t, e), sI(t, [{
    key: "render",
    value: function() {
      var n = this.props, a = n.offset, i = n.layout, o = n.width, u = n.dataKey, l = n.data, s = n.dataPointFormatter, f = n.xAxis, c = n.yAxis, d = iI(n, Qk), h = pe(d, !1);
      this.props.direction === "x" && f.type !== "number" && ln();
      var y = l.map(function(v) {
        var p = s(v, u), g = p.x, b = p.y, w = p.value, _ = p.errorVal;
        if (!_)
          return null;
        var m = [], O, x;
        if (Array.isArray(_)) {
          var S = eI(_, 2);
          O = S[0], x = S[1];
        } else
          O = x = _;
        if (i === "vertical") {
          var T = f.scale, C = b + a, A = C + o, N = C - o, $ = T(w - O), D = T(w + x);
          m.push({
            x1: D,
            y1: A,
            x2: D,
            y2: N
          }), m.push({
            x1: $,
            y1: C,
            x2: D,
            y2: C
          }), m.push({
            x1: $,
            y1: A,
            x2: $,
            y2: N
          });
        } else if (i === "horizontal") {
          var R = c.scale, L = g + a, z = L - o, F = L + o, W = R(w - O), X = R(w + x);
          m.push({
            x1: z,
            y1: X,
            x2: F,
            y2: X
          }), m.push({
            x1: L,
            y1: W,
            x2: L,
            y2: X
          }), m.push({
            x1: z,
            y1: W,
            x2: F,
            y2: W
          });
        }
        return /* @__PURE__ */ M.createElement(Ie, Fo({
          className: "recharts-errorBar",
          key: "bar-".concat(m.map(function(J) {
            return "".concat(J.x1, "-").concat(J.x2, "-").concat(J.y1, "-").concat(J.y2);
          }))
        }, h), m.map(function(J) {
          return /* @__PURE__ */ M.createElement("line", Fo({}, J, {
            key: "line-".concat(J.x1, "-").concat(J.x2, "-").concat(J.y1, "-").concat(J.y2)
          }));
        }));
      });
      return /* @__PURE__ */ M.createElement(Ie, {
        className: "recharts-errorBars"
      }, y);
    }
  }]);
})(M.Component);
o_(Lu, "defaultProps", {
  stroke: "black",
  strokeWidth: 1.5,
  width: 5,
  offset: 0,
  layout: "horizontal"
});
o_(Lu, "displayName", "ErrorBar");
function ii(e) {
  "@babel/helpers - typeof";
  return ii = typeof Symbol == "function" && typeof Symbol.iterator == "symbol" ? function(t) {
    return typeof t;
  } : function(t) {
    return t && typeof Symbol == "function" && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t;
  }, ii(e);
}
function bb(e, t) {
  var r = Object.keys(e);
  if (Object.getOwnPropertySymbols) {
    var n = Object.getOwnPropertySymbols(e);
    t && (n = n.filter(function(a) {
      return Object.getOwnPropertyDescriptor(e, a).enumerable;
    })), r.push.apply(r, n);
  }
  return r;
}
function Kr(e) {
  for (var t = 1; t < arguments.length; t++) {
    var r = arguments[t] != null ? arguments[t] : {};
    t % 2 ? bb(Object(r), !0).forEach(function(n) {
      vI(e, n, r[n]);
    }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(r)) : bb(Object(r)).forEach(function(n) {
      Object.defineProperty(e, n, Object.getOwnPropertyDescriptor(r, n));
    });
  }
  return e;
}
function vI(e, t, r) {
  return t = yI(t), t in e ? Object.defineProperty(e, t, { value: r, enumerable: !0, configurable: !0, writable: !0 }) : e[t] = r, e;
}
function yI(e) {
  var t = mI(e, "string");
  return ii(t) == "symbol" ? t : t + "";
}
function mI(e, t) {
  if (ii(e) != "object" || !e) return e;
  var r = e[Symbol.toPrimitive];
  if (r !== void 0) {
    var n = r.call(e, t);
    if (ii(n) != "object") return n;
    throw new TypeError("@@toPrimitive must return a primitive value.");
  }
  return (t === "string" ? String : Number)(e);
}
var l_ = function(t) {
  var r = t.children, n = t.formattedGraphicalItems, a = t.legendWidth, i = t.legendContent, o = vt(r, rn);
  if (!o)
    return null;
  var u = rn.defaultProps, l = u !== void 0 ? Kr(Kr({}, u), o.props) : {}, s;
  return o.props && o.props.payload ? s = o.props && o.props.payload : i === "children" ? s = (n || []).reduce(function(f, c) {
    var d = c.item, h = c.props, y = h.sectors || h.data || [];
    return f.concat(y.map(function(v) {
      return {
        type: o.props.iconType || d.props.legendType,
        value: v.name,
        color: v.fill,
        payload: v
      };
    }));
  }, []) : s = (n || []).map(function(f) {
    var c = f.item, d = c.type.defaultProps, h = d !== void 0 ? Kr(Kr({}, d), c.props) : {}, y = h.dataKey, v = h.name, p = h.legendType, g = h.hide;
    return {
      inactive: g,
      dataKey: y,
      type: l.iconType || p || "square",
      color: Rp(c),
      value: v || y,
      // @ts-expect-error property strokeDasharray is required in Payload but optional in props
      payload: h
    };
  }), Kr(Kr(Kr({}, l), rn.getWithHeight(o, a)), {}, {
    payload: s,
    item: o
  });
};
function oi(e) {
  "@babel/helpers - typeof";
  return oi = typeof Symbol == "function" && typeof Symbol.iterator == "symbol" ? function(t) {
    return typeof t;
  } : function(t) {
    return t && typeof Symbol == "function" && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t;
  }, oi(e);
}
function xb(e) {
  return wI(e) || xI(e) || bI(e) || gI();
}
function gI() {
  throw new TypeError(`Invalid attempt to spread non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`);
}
function bI(e, t) {
  if (e) {
    if (typeof e == "string") return Ud(e, t);
    var r = Object.prototype.toString.call(e).slice(8, -1);
    if (r === "Object" && e.constructor && (r = e.constructor.name), r === "Map" || r === "Set") return Array.from(e);
    if (r === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r)) return Ud(e, t);
  }
}
function xI(e) {
  if (typeof Symbol < "u" && e[Symbol.iterator] != null || e["@@iterator"] != null) return Array.from(e);
}
function wI(e) {
  if (Array.isArray(e)) return Ud(e);
}
function Ud(e, t) {
  (t == null || t > e.length) && (t = e.length);
  for (var r = 0, n = new Array(t); r < t; r++) n[r] = e[r];
  return n;
}
function wb(e, t) {
  var r = Object.keys(e);
  if (Object.getOwnPropertySymbols) {
    var n = Object.getOwnPropertySymbols(e);
    t && (n = n.filter(function(a) {
      return Object.getOwnPropertyDescriptor(e, a).enumerable;
    })), r.push.apply(r, n);
  }
  return r;
}
function Le(e) {
  for (var t = 1; t < arguments.length; t++) {
    var r = arguments[t] != null ? arguments[t] : {};
    t % 2 ? wb(Object(r), !0).forEach(function(n) {
      $n(e, n, r[n]);
    }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(r)) : wb(Object(r)).forEach(function(n) {
      Object.defineProperty(e, n, Object.getOwnPropertyDescriptor(r, n));
    });
  }
  return e;
}
function $n(e, t, r) {
  return t = OI(t), t in e ? Object.defineProperty(e, t, { value: r, enumerable: !0, configurable: !0, writable: !0 }) : e[t] = r, e;
}
function OI(e) {
  var t = _I(e, "string");
  return oi(t) == "symbol" ? t : t + "";
}
function _I(e, t) {
  if (oi(e) != "object" || !e) return e;
  var r = e[Symbol.toPrimitive];
  if (r !== void 0) {
    var n = r.call(e, t);
    if (oi(n) != "object") return n;
    throw new TypeError("@@toPrimitive must return a primitive value.");
  }
  return (t === "string" ? String : Number)(e);
}
function gt(e, t, r) {
  return me(e) || me(t) ? r : Ve(t) ? Tt(e, t, r) : fe(t) ? t(e) : r;
}
function Da(e, t, r, n) {
  var a = wk(e, function(u) {
    return gt(u, t);
  });
  if (r === "number") {
    var i = a.filter(function(u) {
      return H(u) || parseFloat(u);
    });
    return i.length ? [ku(i), Nr(i)] : [1 / 0, -1 / 0];
  }
  var o = n ? a.filter(function(u) {
    return !me(u);
  }) : a;
  return o.map(function(u) {
    return Ve(u) || u instanceof Date ? u : "";
  });
}
var SI = function(t) {
  var r, n = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : [], a = arguments.length > 2 ? arguments[2] : void 0, i = arguments.length > 3 ? arguments[3] : void 0, o = -1, u = (r = n?.length) !== null && r !== void 0 ? r : 0;
  if (u <= 1)
    return 0;
  if (i && i.axisType === "angleAxis" && Math.abs(Math.abs(i.range[1] - i.range[0]) - 360) <= 1e-6)
    for (var l = i.range, s = 0; s < u; s++) {
      var f = s > 0 ? a[s - 1].coordinate : a[u - 1].coordinate, c = a[s].coordinate, d = s >= u - 1 ? a[0].coordinate : a[s + 1].coordinate, h = void 0;
      if (Dt(c - f) !== Dt(d - c)) {
        var y = [];
        if (Dt(d - c) === Dt(l[1] - l[0])) {
          h = d;
          var v = c + l[1] - l[0];
          y[0] = Math.min(v, (v + f) / 2), y[1] = Math.max(v, (v + f) / 2);
        } else {
          h = f;
          var p = d + l[1] - l[0];
          y[0] = Math.min(c, (p + c) / 2), y[1] = Math.max(c, (p + c) / 2);
        }
        var g = [Math.min(c, (h + c) / 2), Math.max(c, (h + c) / 2)];
        if (t > g[0] && t <= g[1] || t >= y[0] && t <= y[1]) {
          o = a[s].index;
          break;
        }
      } else {
        var b = Math.min(f, d), w = Math.max(f, d);
        if (t > (b + c) / 2 && t <= (w + c) / 2) {
          o = a[s].index;
          break;
        }
      }
    }
  else
    for (var _ = 0; _ < u; _++)
      if (_ === 0 && t <= (n[_].coordinate + n[_ + 1].coordinate) / 2 || _ > 0 && _ < u - 1 && t > (n[_].coordinate + n[_ - 1].coordinate) / 2 && t <= (n[_].coordinate + n[_ + 1].coordinate) / 2 || _ === u - 1 && t > (n[_].coordinate + n[_ - 1].coordinate) / 2) {
        o = n[_].index;
        break;
      }
  return o;
}, Rp = function(t) {
  var r, n = t, a = n.type.displayName, i = (r = t.type) !== null && r !== void 0 && r.defaultProps ? Le(Le({}, t.type.defaultProps), t.props) : t.props, o = i.stroke, u = i.fill, l;
  switch (a) {
    case "Line":
      l = o;
      break;
    case "Area":
    case "Radar":
      l = o && o !== "none" ? o : u;
      break;
    default:
      l = u;
      break;
  }
  return l;
}, PI = function(t) {
  var r = t.barSize, n = t.totalSize, a = t.stackGroups, i = a === void 0 ? {} : a;
  if (!i)
    return {};
  for (var o = {}, u = Object.keys(i), l = 0, s = u.length; l < s; l++)
    for (var f = i[u[l]].stackGroups, c = Object.keys(f), d = 0, h = c.length; d < h; d++) {
      var y = f[c[d]], v = y.items, p = y.cateAxisId, g = v.filter(function(x) {
        return fr(x.type).indexOf("Bar") >= 0;
      });
      if (g && g.length) {
        var b = g[0].type.defaultProps, w = b !== void 0 ? Le(Le({}, b), g[0].props) : g[0].props, _ = w.barSize, m = w[p];
        o[m] || (o[m] = []);
        var O = me(_) ? r : _;
        o[m].push({
          item: g[0],
          stackList: g.slice(1),
          barSize: me(O) ? void 0 : on(O, n, 0)
        });
      }
    }
  return o;
}, AI = function(t) {
  var r = t.barGap, n = t.barCategoryGap, a = t.bandSize, i = t.sizeList, o = i === void 0 ? [] : i, u = t.maxBarSize, l = o.length;
  if (l < 1) return null;
  var s = on(r, a, 0, !0), f, c = [];
  if (o[0].barSize === +o[0].barSize) {
    var d = !1, h = a / l, y = o.reduce(function(_, m) {
      return _ + m.barSize || 0;
    }, 0);
    y += (l - 1) * s, y >= a && (y -= (l - 1) * s, s = 0), y >= a && h > 0 && (d = !0, h *= 0.9, y = l * h);
    var v = (a - y) / 2 >> 0, p = {
      offset: v - s,
      size: 0
    };
    f = o.reduce(function(_, m) {
      var O = {
        item: m.item,
        position: {
          offset: p.offset + p.size + s,
          // @ts-expect-error the type check above does not check for type number explicitly
          size: d ? h : m.barSize
        }
      }, x = [].concat(xb(_), [O]);
      return p = x[x.length - 1].position, m.stackList && m.stackList.length && m.stackList.forEach(function(S) {
        x.push({
          item: S,
          position: p
        });
      }), x;
    }, c);
  } else {
    var g = on(n, a, 0, !0);
    a - 2 * g - (l - 1) * s <= 0 && (s = 0);
    var b = (a - 2 * g - (l - 1) * s) / l;
    b > 1 && (b >>= 0);
    var w = u === +u ? Math.min(b, u) : b;
    f = o.reduce(function(_, m, O) {
      var x = [].concat(xb(_), [{
        item: m.item,
        position: {
          offset: g + (b + s) * O + (b - w) / 2,
          size: w
        }
      }]);
      return m.stackList && m.stackList.length && m.stackList.forEach(function(S) {
        x.push({
          item: S,
          position: x[x.length - 1].position
        });
      }), x;
    }, c);
  }
  return f;
}, EI = function(t, r, n, a) {
  var i = n.children, o = n.width, u = n.margin, l = o - (u.left || 0) - (u.right || 0), s = l_({
    children: i,
    legendWidth: l
  });
  if (s) {
    var f = a || {}, c = f.width, d = f.height, h = s.align, y = s.verticalAlign, v = s.layout;
    if ((v === "vertical" || v === "horizontal" && y === "middle") && h !== "center" && H(t[h]))
      return Le(Le({}, t), {}, $n({}, h, t[h] + (c || 0)));
    if ((v === "horizontal" || v === "vertical" && h === "center") && y !== "middle" && H(t[y]))
      return Le(Le({}, t), {}, $n({}, y, t[y] + (d || 0)));
  }
  return t;
}, TI = function(t, r, n) {
  return me(r) ? !0 : t === "horizontal" ? r === "yAxis" : t === "vertical" || n === "x" ? r === "xAxis" : n === "y" ? r === "yAxis" : !0;
}, s_ = function(t, r, n, a, i) {
  var o = r.props.children, u = qt(o, Lu).filter(function(s) {
    return TI(a, i, s.props.direction);
  });
  if (u && u.length) {
    var l = u.map(function(s) {
      return s.props.dataKey;
    });
    return t.reduce(function(s, f) {
      var c = gt(f, n);
      if (me(c)) return s;
      var d = Array.isArray(c) ? [ku(c), Nr(c)] : [c, c], h = l.reduce(function(y, v) {
        var p = gt(f, v, 0), g = d[0] - Math.abs(Array.isArray(p) ? p[0] : p), b = d[1] + Math.abs(Array.isArray(p) ? p[1] : p);
        return [Math.min(g, y[0]), Math.max(b, y[1])];
      }, [1 / 0, -1 / 0]);
      return [Math.min(h[0], s[0]), Math.max(h[1], s[1])];
    }, [1 / 0, -1 / 0]);
  }
  return null;
}, MI = function(t, r, n, a, i) {
  var o = r.map(function(u) {
    return s_(t, u, n, i, a);
  }).filter(function(u) {
    return !me(u);
  });
  return o && o.length ? o.reduce(function(u, l) {
    return [Math.min(u[0], l[0]), Math.max(u[1], l[1])];
  }, [1 / 0, -1 / 0]) : null;
}, c_ = function(t, r, n, a, i) {
  var o = r.map(function(l) {
    var s = l.props.dataKey;
    return n === "number" && s && s_(t, l, s, a) || Da(t, s, n, i);
  });
  if (n === "number")
    return o.reduce(
      // @ts-expect-error if (type === number) means that the domain is numerical type
      // - but this link is missing in the type definition
      function(l, s) {
        return [Math.min(l[0], s[0]), Math.max(l[1], s[1])];
      },
      [1 / 0, -1 / 0]
    );
  var u = {};
  return o.reduce(function(l, s) {
    for (var f = 0, c = s.length; f < c; f++)
      u[s[f]] || (u[s[f]] = !0, l.push(s[f]));
    return l;
  }, []);
}, f_ = function(t, r) {
  return t === "horizontal" && r === "xAxis" || t === "vertical" && r === "yAxis" || t === "centric" && r === "angleAxis" || t === "radial" && r === "radiusAxis";
}, d_ = function(t, r, n, a) {
  if (a)
    return t.map(function(l) {
      return l.coordinate;
    });
  var i, o, u = t.map(function(l) {
    return l.coordinate === r && (i = !0), l.coordinate === n && (o = !0), l.coordinate;
  });
  return i || u.push(r), o || u.push(n), u;
}, cr = function(t, r, n) {
  if (!t) return null;
  var a = t.scale, i = t.duplicateDomain, o = t.type, u = t.range, l = t.realScaleType === "scaleBand" ? a.bandwidth() / 2 : 2, s = (r || n) && o === "category" && a.bandwidth ? a.bandwidth() / l : 0;
  if (s = t.axisType === "angleAxis" && u?.length >= 2 ? Dt(u[0] - u[1]) * 2 * s : s, r && (t.ticks || t.niceTicks)) {
    var f = (t.ticks || t.niceTicks).map(function(c) {
      var d = i ? i.indexOf(c) : c;
      return {
        // If the scaleContent is not a number, the coordinate will be NaN.
        // That could be the case for example with a PointScale and a string as domain.
        coordinate: a(d) + s,
        value: c,
        offset: s
      };
    });
    return f.filter(function(c) {
      return !ia(c.coordinate);
    });
  }
  return t.isCategorical && t.categoricalDomain ? t.categoricalDomain.map(function(c, d) {
    return {
      coordinate: a(c) + s,
      value: c,
      index: d,
      offset: s
    };
  }) : a.ticks && !n ? a.ticks(t.tickCount).map(function(c) {
    return {
      coordinate: a(c) + s,
      value: c,
      offset: s
    };
  }) : a.domain().map(function(c, d) {
    return {
      coordinate: a(c) + s,
      value: i ? i[c] : c,
      index: d,
      offset: s
    };
  });
}, Tf = /* @__PURE__ */ new WeakMap(), no = function(t, r) {
  if (typeof r != "function")
    return t;
  Tf.has(t) || Tf.set(t, /* @__PURE__ */ new WeakMap());
  var n = Tf.get(t);
  if (n.has(r))
    return n.get(r);
  var a = function() {
    t.apply(void 0, arguments), r.apply(void 0, arguments);
  };
  return n.set(r, a), a;
}, jI = function(t, r, n) {
  var a = t.scale, i = t.type, o = t.layout, u = t.axisType;
  if (a === "auto")
    return o === "radial" && u === "radiusAxis" ? {
      scale: Za(),
      realScaleType: "band"
    } : o === "radial" && u === "angleAxis" ? {
      scale: Io(),
      realScaleType: "linear"
    } : i === "category" && r && (r.indexOf("LineChart") >= 0 || r.indexOf("AreaChart") >= 0 || r.indexOf("ComposedChart") >= 0 && !n) ? {
      scale: Ia(),
      realScaleType: "point"
    } : i === "category" ? {
      scale: Za(),
      realScaleType: "band"
    } : {
      scale: Io(),
      realScaleType: "linear"
    };
  if (Ci(a)) {
    var l = "scale".concat(Ou(a));
    return {
      scale: (ob[l] || Ia)(),
      realScaleType: ob[l] ? l : "point"
    };
  }
  return fe(a) ? {
    scale: a
  } : {
    scale: Ia(),
    realScaleType: "point"
  };
}, Ob = 1e-4, NI = function(t) {
  var r = t.domain();
  if (!(!r || r.length <= 2)) {
    var n = r.length, a = t.range(), i = Math.min(a[0], a[1]) - Ob, o = Math.max(a[0], a[1]) + Ob, u = t(r[0]), l = t(r[n - 1]);
    (u < i || u > o || l < i || l > o) && t.domain([r[0], r[n - 1]]);
  }
}, CI = function(t, r) {
  if (!t)
    return null;
  for (var n = 0, a = t.length; n < a; n++)
    if (t[n].item === r)
      return t[n].position;
  return null;
}, $I = function(t, r) {
  if (!r || r.length !== 2 || !H(r[0]) || !H(r[1]))
    return t;
  var n = Math.min(r[0], r[1]), a = Math.max(r[0], r[1]), i = [t[0], t[1]];
  return (!H(t[0]) || t[0] < n) && (i[0] = n), (!H(t[1]) || t[1] > a) && (i[1] = a), i[0] > a && (i[0] = a), i[1] < n && (i[1] = n), i;
}, RI = function(t) {
  var r = t.length;
  if (!(r <= 0))
    for (var n = 0, a = t[0].length; n < a; ++n)
      for (var i = 0, o = 0, u = 0; u < r; ++u) {
        var l = ia(t[u][n][1]) ? t[u][n][0] : t[u][n][1];
        l >= 0 ? (t[u][n][0] = i, t[u][n][1] = i + l, i = t[u][n][1]) : (t[u][n][0] = o, t[u][n][1] = o + l, o = t[u][n][1]);
      }
}, kI = function(t) {
  var r = t.length;
  if (!(r <= 0))
    for (var n = 0, a = t[0].length; n < a; ++n)
      for (var i = 0, o = 0; o < r; ++o) {
        var u = ia(t[o][n][1]) ? t[o][n][0] : t[o][n][1];
        u >= 0 ? (t[o][n][0] = i, t[o][n][1] = i + u, i = t[o][n][1]) : (t[o][n][0] = 0, t[o][n][1] = 0);
      }
}, II = {
  sign: RI,
  // @ts-expect-error definitelytyped types are incorrect
  expand: bM,
  // @ts-expect-error definitelytyped types are incorrect
  none: kn,
  // @ts-expect-error definitelytyped types are incorrect
  silhouette: xM,
  // @ts-expect-error definitelytyped types are incorrect
  wiggle: wM,
  positive: kI
}, DI = function(t, r, n) {
  var a = r.map(function(u) {
    return u.props.dataKey;
  }), i = II[n], o = gM().keys(a).value(function(u, l) {
    return +gt(u, l, 0);
  }).order(gd).offset(i);
  return o(t);
}, LI = function(t, r, n, a, i, o) {
  if (!t)
    return null;
  var u = o ? r.reverse() : r, l = {}, s = u.reduce(function(c, d) {
    var h, y = (h = d.type) !== null && h !== void 0 && h.defaultProps ? Le(Le({}, d.type.defaultProps), d.props) : d.props, v = y.stackId, p = y.hide;
    if (p)
      return c;
    var g = y[n], b = c[g] || {
      hasStack: !1,
      stackGroups: {}
    };
    if (Ve(v)) {
      var w = b.stackGroups[v] || {
        numericAxisId: n,
        cateAxisId: a,
        items: []
      };
      w.items.push(d), b.hasStack = !0, b.stackGroups[v] = w;
    } else
      b.stackGroups[$i("_stackId_")] = {
        numericAxisId: n,
        cateAxisId: a,
        items: [d]
      };
    return Le(Le({}, c), {}, $n({}, g, b));
  }, l), f = {};
  return Object.keys(s).reduce(function(c, d) {
    var h = s[d];
    if (h.hasStack) {
      var y = {};
      h.stackGroups = Object.keys(h.stackGroups).reduce(function(v, p) {
        var g = h.stackGroups[p];
        return Le(Le({}, v), {}, $n({}, p, {
          numericAxisId: n,
          cateAxisId: a,
          items: g.items,
          stackedData: DI(t, g.items, i)
        }));
      }, y);
    }
    return Le(Le({}, c), {}, $n({}, d, h));
  }, f);
}, qI = function(t, r) {
  var n = r.realScaleType, a = r.type, i = r.tickCount, o = r.originalDomain, u = r.allowDecimals, l = n || r.scale;
  if (l !== "auto" && l !== "linear")
    return null;
  if (i && a === "number" && o && (o[0] === "auto" || o[1] === "auto")) {
    var s = t.domain();
    if (!s.length)
      return null;
    var f = Yk(s, i, u);
    return t.domain([ku(f), Nr(f)]), {
      niceTicks: f
    };
  }
  if (i && a === "number") {
    var c = t.domain(), d = Zk(c, i, u);
    return {
      niceTicks: d
    };
  }
  return null;
};
function _b(e) {
  var t = e.axis, r = e.ticks, n = e.bandSize, a = e.entry, i = e.index, o = e.dataKey;
  if (t.type === "category") {
    if (!t.allowDuplicatedCategory && t.dataKey && !me(a[t.dataKey])) {
      var u = yo(r, "value", a[t.dataKey]);
      if (u)
        return u.coordinate + n / 2;
    }
    return r[i] ? r[i].coordinate + n / 2 : null;
  }
  var l = gt(a, me(o) ? t.dataKey : o);
  return me(l) ? null : t.scale(l);
}
var Sb = function(t) {
  var r = t.axis, n = t.ticks, a = t.offset, i = t.bandSize, o = t.entry, u = t.index;
  if (r.type === "category")
    return n[u] ? n[u].coordinate + a : null;
  var l = gt(o, r.dataKey, r.domain[u]);
  return me(l) ? null : r.scale(l) - i / 2 + a;
}, BI = function(t) {
  var r = t.numericAxis, n = r.scale.domain();
  if (r.type === "number") {
    var a = Math.min(n[0], n[1]), i = Math.max(n[0], n[1]);
    return a <= 0 && i >= 0 ? 0 : i < 0 ? i : a;
  }
  return n[0];
}, FI = function(t, r) {
  var n, a = (n = t.type) !== null && n !== void 0 && n.defaultProps ? Le(Le({}, t.type.defaultProps), t.props) : t.props, i = a.stackId;
  if (Ve(i)) {
    var o = r[i];
    if (o) {
      var u = o.items.indexOf(t);
      return u >= 0 ? o.stackedData[u] : null;
    }
  }
  return null;
}, zI = function(t) {
  return t.reduce(function(r, n) {
    return [ku(n.concat([r[0]]).filter(H)), Nr(n.concat([r[1]]).filter(H))];
  }, [1 / 0, -1 / 0]);
}, h_ = function(t, r, n) {
  return Object.keys(t).reduce(function(a, i) {
    var o = t[i], u = o.stackedData, l = u.reduce(function(s, f) {
      var c = zI(f.slice(r, n + 1));
      return [Math.min(s[0], c[0]), Math.max(s[1], c[1])];
    }, [1 / 0, -1 / 0]);
    return [Math.min(l[0], a[0]), Math.max(l[1], a[1])];
  }, [1 / 0, -1 / 0]).map(function(a) {
    return a === 1 / 0 || a === -1 / 0 ? 0 : a;
  });
}, Pb = /^dataMin[\s]*-[\s]*([0-9]+([.]{1}[0-9]+){0,1})$/, Ab = /^dataMax[\s]*\+[\s]*([0-9]+([.]{1}[0-9]+){0,1})$/, Wd = function(t, r, n) {
  if (fe(t))
    return t(r, n);
  if (!Array.isArray(t))
    return r;
  var a = [];
  if (H(t[0]))
    a[0] = n ? t[0] : Math.min(t[0], r[0]);
  else if (Pb.test(t[0])) {
    var i = +Pb.exec(t[0])[1];
    a[0] = r[0] - i;
  } else fe(t[0]) ? a[0] = t[0](r[0]) : a[0] = r[0];
  if (H(t[1]))
    a[1] = n ? t[1] : Math.max(t[1], r[1]);
  else if (Ab.test(t[1])) {
    var o = +Ab.exec(t[1])[1];
    a[1] = r[1] + o;
  } else fe(t[1]) ? a[1] = t[1](r[1]) : a[1] = r[1];
  return a;
}, Uo = function(t, r, n) {
  if (t && t.scale && t.scale.bandwidth) {
    var a = t.scale.bandwidth();
    if (!n || a > 0)
      return a;
  }
  if (t && r && r.length >= 2) {
    for (var i = lp(r, function(c) {
      return c.coordinate;
    }), o = 1 / 0, u = 1, l = i.length; u < l; u++) {
      var s = i[u], f = i[u - 1];
      o = Math.min((s.coordinate || 0) - (f.coordinate || 0), o);
    }
    return o === 1 / 0 ? 0 : o;
  }
  return n ? void 0 : 0;
}, Eb = function(t, r, n) {
  return !t || !t.length || ri(t, Tt(n, "type.defaultProps.domain")) ? r : t;
}, p_ = function(t, r) {
  var n = t.type.defaultProps ? Le(Le({}, t.type.defaultProps), t.props) : t.props, a = n.dataKey, i = n.name, o = n.unit, u = n.formatter, l = n.tooltipType, s = n.chartType, f = n.hide;
  return Le(Le({}, pe(t, !1)), {}, {
    dataKey: a,
    unit: o,
    formatter: u,
    name: i || a,
    color: Rp(t),
    value: gt(r, a),
    type: l,
    payload: r,
    chartType: s,
    hide: f
  });
};
function ui(e) {
  "@babel/helpers - typeof";
  return ui = typeof Symbol == "function" && typeof Symbol.iterator == "symbol" ? function(t) {
    return typeof t;
  } : function(t) {
    return t && typeof Symbol == "function" && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t;
  }, ui(e);
}
function Tb(e, t) {
  var r = Object.keys(e);
  if (Object.getOwnPropertySymbols) {
    var n = Object.getOwnPropertySymbols(e);
    t && (n = n.filter(function(a) {
      return Object.getOwnPropertyDescriptor(e, a).enumerable;
    })), r.push.apply(r, n);
  }
  return r;
}
function Mb(e) {
  for (var t = 1; t < arguments.length; t++) {
    var r = arguments[t] != null ? arguments[t] : {};
    t % 2 ? Tb(Object(r), !0).forEach(function(n) {
      UI(e, n, r[n]);
    }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(r)) : Tb(Object(r)).forEach(function(n) {
      Object.defineProperty(e, n, Object.getOwnPropertyDescriptor(r, n));
    });
  }
  return e;
}
function UI(e, t, r) {
  return t = WI(t), t in e ? Object.defineProperty(e, t, { value: r, enumerable: !0, configurable: !0, writable: !0 }) : e[t] = r, e;
}
function WI(e) {
  var t = HI(e, "string");
  return ui(t) == "symbol" ? t : t + "";
}
function HI(e, t) {
  if (ui(e) != "object" || !e) return e;
  var r = e[Symbol.toPrimitive];
  if (r !== void 0) {
    var n = r.call(e, t);
    if (ui(n) != "object") return n;
    throw new TypeError("@@toPrimitive must return a primitive value.");
  }
  return (t === "string" ? String : Number)(e);
}
var Wo = Math.PI / 180, GI = function(t) {
  return t * 180 / Math.PI;
}, tt = function(t, r, n, a) {
  return {
    x: t + Math.cos(-Wo * a) * n,
    y: r + Math.sin(-Wo * a) * n
  };
}, KI = function(t, r) {
  var n = t.x, a = t.y, i = r.x, o = r.y;
  return Math.sqrt(Math.pow(n - i, 2) + Math.pow(a - o, 2));
}, VI = function(t, r) {
  var n = t.x, a = t.y, i = r.cx, o = r.cy, u = KI({
    x: n,
    y: a
  }, {
    x: i,
    y: o
  });
  if (u <= 0)
    return {
      radius: u
    };
  var l = (n - i) / u, s = Math.acos(l);
  return a > o && (s = 2 * Math.PI - s), {
    radius: u,
    angle: GI(s),
    angleInRadian: s
  };
}, XI = function(t) {
  var r = t.startAngle, n = t.endAngle, a = Math.floor(r / 360), i = Math.floor(n / 360), o = Math.min(a, i);
  return {
    startAngle: r - o * 360,
    endAngle: n - o * 360
  };
}, YI = function(t, r) {
  var n = r.startAngle, a = r.endAngle, i = Math.floor(n / 360), o = Math.floor(a / 360), u = Math.min(i, o);
  return t + u * 360;
}, jb = function(t, r) {
  var n = t.x, a = t.y, i = VI({
    x: n,
    y: a
  }, r), o = i.radius, u = i.angle, l = r.innerRadius, s = r.outerRadius;
  if (o < l || o > s)
    return !1;
  if (o === 0)
    return !0;
  var f = XI(r), c = f.startAngle, d = f.endAngle, h = u, y;
  if (c <= d) {
    for (; h > d; )
      h -= 360;
    for (; h < c; )
      h += 360;
    y = h >= c && h <= d;
  } else {
    for (; h > c; )
      h -= 360;
    for (; h < d; )
      h += 360;
    y = h >= d && h <= c;
  }
  return y ? Mb(Mb({}, r), {}, {
    radius: o,
    angle: YI(h, r)
  }) : null;
};
function li(e) {
  "@babel/helpers - typeof";
  return li = typeof Symbol == "function" && typeof Symbol.iterator == "symbol" ? function(t) {
    return typeof t;
  } : function(t) {
    return t && typeof Symbol == "function" && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t;
  }, li(e);
}
var ZI = ["offset"];
function JI(e) {
  return rD(e) || tD(e) || eD(e) || QI();
}
function QI() {
  throw new TypeError(`Invalid attempt to spread non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`);
}
function eD(e, t) {
  if (e) {
    if (typeof e == "string") return Hd(e, t);
    var r = Object.prototype.toString.call(e).slice(8, -1);
    if (r === "Object" && e.constructor && (r = e.constructor.name), r === "Map" || r === "Set") return Array.from(e);
    if (r === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r)) return Hd(e, t);
  }
}
function tD(e) {
  if (typeof Symbol < "u" && e[Symbol.iterator] != null || e["@@iterator"] != null) return Array.from(e);
}
function rD(e) {
  if (Array.isArray(e)) return Hd(e);
}
function Hd(e, t) {
  (t == null || t > e.length) && (t = e.length);
  for (var r = 0, n = new Array(t); r < t; r++) n[r] = e[r];
  return n;
}
function nD(e, t) {
  if (e == null) return {};
  var r = aD(e, t), n, a;
  if (Object.getOwnPropertySymbols) {
    var i = Object.getOwnPropertySymbols(e);
    for (a = 0; a < i.length; a++)
      n = i[a], !(t.indexOf(n) >= 0) && Object.prototype.propertyIsEnumerable.call(e, n) && (r[n] = e[n]);
  }
  return r;
}
function aD(e, t) {
  if (e == null) return {};
  var r = {};
  for (var n in e)
    if (Object.prototype.hasOwnProperty.call(e, n)) {
      if (t.indexOf(n) >= 0) continue;
      r[n] = e[n];
    }
  return r;
}
function Nb(e, t) {
  var r = Object.keys(e);
  if (Object.getOwnPropertySymbols) {
    var n = Object.getOwnPropertySymbols(e);
    t && (n = n.filter(function(a) {
      return Object.getOwnPropertyDescriptor(e, a).enumerable;
    })), r.push.apply(r, n);
  }
  return r;
}
function Ke(e) {
  for (var t = 1; t < arguments.length; t++) {
    var r = arguments[t] != null ? arguments[t] : {};
    t % 2 ? Nb(Object(r), !0).forEach(function(n) {
      iD(e, n, r[n]);
    }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(r)) : Nb(Object(r)).forEach(function(n) {
      Object.defineProperty(e, n, Object.getOwnPropertyDescriptor(r, n));
    });
  }
  return e;
}
function iD(e, t, r) {
  return t = oD(t), t in e ? Object.defineProperty(e, t, { value: r, enumerable: !0, configurable: !0, writable: !0 }) : e[t] = r, e;
}
function oD(e) {
  var t = uD(e, "string");
  return li(t) == "symbol" ? t : t + "";
}
function uD(e, t) {
  if (li(e) != "object" || !e) return e;
  var r = e[Symbol.toPrimitive];
  if (r !== void 0) {
    var n = r.call(e, t);
    if (li(n) != "object") return n;
    throw new TypeError("@@toPrimitive must return a primitive value.");
  }
  return (t === "string" ? String : Number)(e);
}
function si() {
  return si = Object.assign ? Object.assign.bind() : function(e) {
    for (var t = 1; t < arguments.length; t++) {
      var r = arguments[t];
      for (var n in r)
        Object.prototype.hasOwnProperty.call(r, n) && (e[n] = r[n]);
    }
    return e;
  }, si.apply(this, arguments);
}
var lD = function(t) {
  var r = t.value, n = t.formatter, a = me(t.children) ? r : t.children;
  return fe(n) ? n(a) : a;
}, sD = function(t, r) {
  var n = Dt(r - t), a = Math.min(Math.abs(r - t), 360);
  return n * a;
}, cD = function(t, r, n) {
  var a = t.position, i = t.viewBox, o = t.offset, u = t.className, l = i, s = l.cx, f = l.cy, c = l.innerRadius, d = l.outerRadius, h = l.startAngle, y = l.endAngle, v = l.clockWise, p = (c + d) / 2, g = sD(h, y), b = g >= 0 ? 1 : -1, w, _;
  a === "insideStart" ? (w = h + b * o, _ = v) : a === "insideEnd" ? (w = y - b * o, _ = !v) : a === "end" && (w = y + b * o, _ = v), _ = g <= 0 ? _ : !_;
  var m = tt(s, f, p, w), O = tt(s, f, p, w + (_ ? 1 : -1) * 359), x = "M".concat(m.x, ",").concat(m.y, `
    A`).concat(p, ",").concat(p, ",0,1,").concat(_ ? 0 : 1, `,
    `).concat(O.x, ",").concat(O.y), S = me(t.id) ? $i("recharts-radial-line-") : t.id;
  return /* @__PURE__ */ M.createElement("text", si({}, n, {
    dominantBaseline: "central",
    className: _e("recharts-radial-bar-label", u)
  }), /* @__PURE__ */ M.createElement("defs", null, /* @__PURE__ */ M.createElement("path", {
    id: S,
    d: x
  })), /* @__PURE__ */ M.createElement("textPath", {
    xlinkHref: "#".concat(S)
  }, r));
}, fD = function(t) {
  var r = t.viewBox, n = t.offset, a = t.position, i = r, o = i.cx, u = i.cy, l = i.innerRadius, s = i.outerRadius, f = i.startAngle, c = i.endAngle, d = (f + c) / 2;
  if (a === "outside") {
    var h = tt(o, u, s + n, d), y = h.x, v = h.y;
    return {
      x: y,
      y: v,
      textAnchor: y >= o ? "start" : "end",
      verticalAnchor: "middle"
    };
  }
  if (a === "center")
    return {
      x: o,
      y: u,
      textAnchor: "middle",
      verticalAnchor: "middle"
    };
  if (a === "centerTop")
    return {
      x: o,
      y: u,
      textAnchor: "middle",
      verticalAnchor: "start"
    };
  if (a === "centerBottom")
    return {
      x: o,
      y: u,
      textAnchor: "middle",
      verticalAnchor: "end"
    };
  var p = (l + s) / 2, g = tt(o, u, p, d), b = g.x, w = g.y;
  return {
    x: b,
    y: w,
    textAnchor: "middle",
    verticalAnchor: "middle"
  };
}, dD = function(t) {
  var r = t.viewBox, n = t.parentViewBox, a = t.offset, i = t.position, o = r, u = o.x, l = o.y, s = o.width, f = o.height, c = f >= 0 ? 1 : -1, d = c * a, h = c > 0 ? "end" : "start", y = c > 0 ? "start" : "end", v = s >= 0 ? 1 : -1, p = v * a, g = v > 0 ? "end" : "start", b = v > 0 ? "start" : "end";
  if (i === "top") {
    var w = {
      x: u + s / 2,
      y: l - c * a,
      textAnchor: "middle",
      verticalAnchor: h
    };
    return Ke(Ke({}, w), n ? {
      height: Math.max(l - n.y, 0),
      width: s
    } : {});
  }
  if (i === "bottom") {
    var _ = {
      x: u + s / 2,
      y: l + f + d,
      textAnchor: "middle",
      verticalAnchor: y
    };
    return Ke(Ke({}, _), n ? {
      height: Math.max(n.y + n.height - (l + f), 0),
      width: s
    } : {});
  }
  if (i === "left") {
    var m = {
      x: u - p,
      y: l + f / 2,
      textAnchor: g,
      verticalAnchor: "middle"
    };
    return Ke(Ke({}, m), n ? {
      width: Math.max(m.x - n.x, 0),
      height: f
    } : {});
  }
  if (i === "right") {
    var O = {
      x: u + s + p,
      y: l + f / 2,
      textAnchor: b,
      verticalAnchor: "middle"
    };
    return Ke(Ke({}, O), n ? {
      width: Math.max(n.x + n.width - O.x, 0),
      height: f
    } : {});
  }
  var x = n ? {
    width: s,
    height: f
  } : {};
  return i === "insideLeft" ? Ke({
    x: u + p,
    y: l + f / 2,
    textAnchor: b,
    verticalAnchor: "middle"
  }, x) : i === "insideRight" ? Ke({
    x: u + s - p,
    y: l + f / 2,
    textAnchor: g,
    verticalAnchor: "middle"
  }, x) : i === "insideTop" ? Ke({
    x: u + s / 2,
    y: l + d,
    textAnchor: "middle",
    verticalAnchor: y
  }, x) : i === "insideBottom" ? Ke({
    x: u + s / 2,
    y: l + f - d,
    textAnchor: "middle",
    verticalAnchor: h
  }, x) : i === "insideTopLeft" ? Ke({
    x: u + p,
    y: l + d,
    textAnchor: b,
    verticalAnchor: y
  }, x) : i === "insideTopRight" ? Ke({
    x: u + s - p,
    y: l + d,
    textAnchor: g,
    verticalAnchor: y
  }, x) : i === "insideBottomLeft" ? Ke({
    x: u + p,
    y: l + f - d,
    textAnchor: b,
    verticalAnchor: h
  }, x) : i === "insideBottomRight" ? Ke({
    x: u + s - p,
    y: l + f - d,
    textAnchor: g,
    verticalAnchor: h
  }, x) : aa(i) && (H(i.x) || Zr(i.x)) && (H(i.y) || Zr(i.y)) ? Ke({
    x: u + on(i.x, s),
    y: l + on(i.y, f),
    textAnchor: "end",
    verticalAnchor: "end"
  }, x) : Ke({
    x: u + s / 2,
    y: l + f / 2,
    textAnchor: "middle",
    verticalAnchor: "middle"
  }, x);
}, hD = function(t) {
  return "cx" in t && H(t.cx);
};
function ot(e) {
  var t = e.offset, r = t === void 0 ? 5 : t, n = nD(e, ZI), a = Ke({
    offset: r
  }, n), i = a.viewBox, o = a.position, u = a.value, l = a.children, s = a.content, f = a.className, c = f === void 0 ? "" : f, d = a.textBreakAll;
  if (!i || me(u) && me(l) && !/* @__PURE__ */ Lt(s) && !fe(s))
    return null;
  if (/* @__PURE__ */ Lt(s))
    return /* @__PURE__ */ Ue(s, a);
  var h;
  if (fe(s)) {
    if (h = /* @__PURE__ */ ue(s, a), /* @__PURE__ */ Lt(h))
      return h;
  } else
    h = lD(a);
  var y = hD(i), v = pe(a, !0);
  if (y && (o === "insideStart" || o === "insideEnd" || o === "end"))
    return cD(a, h, v);
  var p = y ? fD(a) : dD(a);
  return /* @__PURE__ */ M.createElement(Mo, si({
    className: _e("recharts-label", c)
  }, v, p, {
    breakAll: d
  }), h);
}
ot.displayName = "Label";
var v_ = function(t) {
  var r = t.cx, n = t.cy, a = t.angle, i = t.startAngle, o = t.endAngle, u = t.r, l = t.radius, s = t.innerRadius, f = t.outerRadius, c = t.x, d = t.y, h = t.top, y = t.left, v = t.width, p = t.height, g = t.clockWise, b = t.labelViewBox;
  if (b)
    return b;
  if (H(v) && H(p)) {
    if (H(c) && H(d))
      return {
        x: c,
        y: d,
        width: v,
        height: p
      };
    if (H(h) && H(y))
      return {
        x: h,
        y,
        width: v,
        height: p
      };
  }
  return H(c) && H(d) ? {
    x: c,
    y: d,
    width: 0,
    height: 0
  } : H(r) && H(n) ? {
    cx: r,
    cy: n,
    startAngle: i || a || 0,
    endAngle: o || a || 0,
    innerRadius: s || 0,
    outerRadius: f || l || u || 0,
    clockWise: g
  } : t.viewBox ? t.viewBox : {};
}, pD = function(t, r) {
  return t ? t === !0 ? /* @__PURE__ */ M.createElement(ot, {
    key: "label-implicit",
    viewBox: r
  }) : Ve(t) ? /* @__PURE__ */ M.createElement(ot, {
    key: "label-implicit",
    viewBox: r,
    value: t
  }) : /* @__PURE__ */ Lt(t) ? t.type === ot ? /* @__PURE__ */ Ue(t, {
    key: "label-implicit",
    viewBox: r
  }) : /* @__PURE__ */ M.createElement(ot, {
    key: "label-implicit",
    content: t,
    viewBox: r
  }) : fe(t) ? /* @__PURE__ */ M.createElement(ot, {
    key: "label-implicit",
    content: t,
    viewBox: r
  }) : aa(t) ? /* @__PURE__ */ M.createElement(ot, si({
    viewBox: r
  }, t, {
    key: "label-implicit"
  })) : null : null;
}, vD = function(t, r) {
  var n = arguments.length > 2 && arguments[2] !== void 0 ? arguments[2] : !0;
  if (!t || !t.children && n && !t.label)
    return null;
  var a = t.children, i = v_(t), o = qt(a, ot).map(function(l, s) {
    return /* @__PURE__ */ Ue(l, {
      viewBox: r || i,
      // eslint-disable-next-line react/no-array-index-key
      key: "label-".concat(s)
    });
  });
  if (!n)
    return o;
  var u = pD(t.label, r || i);
  return [u].concat(JI(o));
};
ot.parseViewBox = v_;
ot.renderCallByParent = vD;
var Mf, Cb;
function yD() {
  if (Cb) return Mf;
  Cb = 1;
  function e(t) {
    var r = t == null ? 0 : t.length;
    return r ? t[r - 1] : void 0;
  }
  return Mf = e, Mf;
}
var mD = yD();
const gD = /* @__PURE__ */ $e(mD);
function ci(e) {
  "@babel/helpers - typeof";
  return ci = typeof Symbol == "function" && typeof Symbol.iterator == "symbol" ? function(t) {
    return typeof t;
  } : function(t) {
    return t && typeof Symbol == "function" && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t;
  }, ci(e);
}
var bD = ["valueAccessor"], xD = ["data", "dataKey", "clockWise", "id", "textBreakAll"];
function wD(e) {
  return PD(e) || SD(e) || _D(e) || OD();
}
function OD() {
  throw new TypeError(`Invalid attempt to spread non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`);
}
function _D(e, t) {
  if (e) {
    if (typeof e == "string") return Gd(e, t);
    var r = Object.prototype.toString.call(e).slice(8, -1);
    if (r === "Object" && e.constructor && (r = e.constructor.name), r === "Map" || r === "Set") return Array.from(e);
    if (r === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r)) return Gd(e, t);
  }
}
function SD(e) {
  if (typeof Symbol < "u" && e[Symbol.iterator] != null || e["@@iterator"] != null) return Array.from(e);
}
function PD(e) {
  if (Array.isArray(e)) return Gd(e);
}
function Gd(e, t) {
  (t == null || t > e.length) && (t = e.length);
  for (var r = 0, n = new Array(t); r < t; r++) n[r] = e[r];
  return n;
}
function Ho() {
  return Ho = Object.assign ? Object.assign.bind() : function(e) {
    for (var t = 1; t < arguments.length; t++) {
      var r = arguments[t];
      for (var n in r)
        Object.prototype.hasOwnProperty.call(r, n) && (e[n] = r[n]);
    }
    return e;
  }, Ho.apply(this, arguments);
}
function $b(e, t) {
  var r = Object.keys(e);
  if (Object.getOwnPropertySymbols) {
    var n = Object.getOwnPropertySymbols(e);
    t && (n = n.filter(function(a) {
      return Object.getOwnPropertyDescriptor(e, a).enumerable;
    })), r.push.apply(r, n);
  }
  return r;
}
function Rb(e) {
  for (var t = 1; t < arguments.length; t++) {
    var r = arguments[t] != null ? arguments[t] : {};
    t % 2 ? $b(Object(r), !0).forEach(function(n) {
      AD(e, n, r[n]);
    }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(r)) : $b(Object(r)).forEach(function(n) {
      Object.defineProperty(e, n, Object.getOwnPropertyDescriptor(r, n));
    });
  }
  return e;
}
function AD(e, t, r) {
  return t = ED(t), t in e ? Object.defineProperty(e, t, { value: r, enumerable: !0, configurable: !0, writable: !0 }) : e[t] = r, e;
}
function ED(e) {
  var t = TD(e, "string");
  return ci(t) == "symbol" ? t : t + "";
}
function TD(e, t) {
  if (ci(e) != "object" || !e) return e;
  var r = e[Symbol.toPrimitive];
  if (r !== void 0) {
    var n = r.call(e, t);
    if (ci(n) != "object") return n;
    throw new TypeError("@@toPrimitive must return a primitive value.");
  }
  return (t === "string" ? String : Number)(e);
}
function kb(e, t) {
  if (e == null) return {};
  var r = MD(e, t), n, a;
  if (Object.getOwnPropertySymbols) {
    var i = Object.getOwnPropertySymbols(e);
    for (a = 0; a < i.length; a++)
      n = i[a], !(t.indexOf(n) >= 0) && Object.prototype.propertyIsEnumerable.call(e, n) && (r[n] = e[n]);
  }
  return r;
}
function MD(e, t) {
  if (e == null) return {};
  var r = {};
  for (var n in e)
    if (Object.prototype.hasOwnProperty.call(e, n)) {
      if (t.indexOf(n) >= 0) continue;
      r[n] = e[n];
    }
  return r;
}
var jD = function(t) {
  return Array.isArray(t.value) ? gD(t.value) : t.value;
};
function kr(e) {
  var t = e.valueAccessor, r = t === void 0 ? jD : t, n = kb(e, bD), a = n.data, i = n.dataKey, o = n.clockWise, u = n.id, l = n.textBreakAll, s = kb(n, xD);
  return !a || !a.length ? null : /* @__PURE__ */ M.createElement(Ie, {
    className: "recharts-label-list"
  }, a.map(function(f, c) {
    var d = me(i) ? r(f, c) : gt(f && f.payload, i), h = me(u) ? {} : {
      id: "".concat(u, "-").concat(c)
    };
    return /* @__PURE__ */ M.createElement(ot, Ho({}, pe(f, !0), s, h, {
      parentViewBox: f.parentViewBox,
      value: d,
      textBreakAll: l,
      viewBox: ot.parseViewBox(me(o) ? f : Rb(Rb({}, f), {}, {
        clockWise: o
      })),
      key: "label-".concat(c),
      index: c
    }));
  }));
}
kr.displayName = "LabelList";
function ND(e, t) {
  return e ? e === !0 ? /* @__PURE__ */ M.createElement(kr, {
    key: "labelList-implicit",
    data: t
  }) : /* @__PURE__ */ M.isValidElement(e) || fe(e) ? /* @__PURE__ */ M.createElement(kr, {
    key: "labelList-implicit",
    data: t,
    content: e
  }) : aa(e) ? /* @__PURE__ */ M.createElement(kr, Ho({
    data: t
  }, e, {
    key: "labelList-implicit"
  })) : null : null;
}
function CD(e, t) {
  var r = arguments.length > 2 && arguments[2] !== void 0 ? arguments[2] : !0;
  if (!e || !e.children && r && !e.label)
    return null;
  var n = e.children, a = qt(n, kr).map(function(o, u) {
    return /* @__PURE__ */ Ue(o, {
      data: t,
      // eslint-disable-next-line react/no-array-index-key
      key: "labelList-".concat(u)
    });
  });
  if (!r)
    return a;
  var i = ND(e.label, t);
  return [i].concat(wD(a));
}
kr.renderCallByParent = CD;
function fi(e) {
  "@babel/helpers - typeof";
  return fi = typeof Symbol == "function" && typeof Symbol.iterator == "symbol" ? function(t) {
    return typeof t;
  } : function(t) {
    return t && typeof Symbol == "function" && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t;
  }, fi(e);
}
function Kd() {
  return Kd = Object.assign ? Object.assign.bind() : function(e) {
    for (var t = 1; t < arguments.length; t++) {
      var r = arguments[t];
      for (var n in r)
        Object.prototype.hasOwnProperty.call(r, n) && (e[n] = r[n]);
    }
    return e;
  }, Kd.apply(this, arguments);
}
function Ib(e, t) {
  var r = Object.keys(e);
  if (Object.getOwnPropertySymbols) {
    var n = Object.getOwnPropertySymbols(e);
    t && (n = n.filter(function(a) {
      return Object.getOwnPropertyDescriptor(e, a).enumerable;
    })), r.push.apply(r, n);
  }
  return r;
}
function Db(e) {
  for (var t = 1; t < arguments.length; t++) {
    var r = arguments[t] != null ? arguments[t] : {};
    t % 2 ? Ib(Object(r), !0).forEach(function(n) {
      $D(e, n, r[n]);
    }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(r)) : Ib(Object(r)).forEach(function(n) {
      Object.defineProperty(e, n, Object.getOwnPropertyDescriptor(r, n));
    });
  }
  return e;
}
function $D(e, t, r) {
  return t = RD(t), t in e ? Object.defineProperty(e, t, { value: r, enumerable: !0, configurable: !0, writable: !0 }) : e[t] = r, e;
}
function RD(e) {
  var t = kD(e, "string");
  return fi(t) == "symbol" ? t : t + "";
}
function kD(e, t) {
  if (fi(e) != "object" || !e) return e;
  var r = e[Symbol.toPrimitive];
  if (r !== void 0) {
    var n = r.call(e, t);
    if (fi(n) != "object") return n;
    throw new TypeError("@@toPrimitive must return a primitive value.");
  }
  return (t === "string" ? String : Number)(e);
}
var ID = function(t, r) {
  var n = Dt(r - t), a = Math.min(Math.abs(r - t), 359.999);
  return n * a;
}, ao = function(t) {
  var r = t.cx, n = t.cy, a = t.radius, i = t.angle, o = t.sign, u = t.isExternal, l = t.cornerRadius, s = t.cornerIsExternal, f = l * (u ? 1 : -1) + a, c = Math.asin(l / f) / Wo, d = s ? i : i + o * c, h = tt(r, n, f, d), y = tt(r, n, a, d), v = s ? i - o * c : i, p = tt(r, n, f * Math.cos(c * Wo), v);
  return {
    center: h,
    circleTangency: y,
    lineTangency: p,
    theta: c
  };
}, y_ = function(t) {
  var r = t.cx, n = t.cy, a = t.innerRadius, i = t.outerRadius, o = t.startAngle, u = t.endAngle, l = ID(o, u), s = o + l, f = tt(r, n, i, o), c = tt(r, n, i, s), d = "M ".concat(f.x, ",").concat(f.y, `
    A `).concat(i, ",").concat(i, `,0,
    `).concat(+(Math.abs(l) > 180), ",").concat(+(o > s), `,
    `).concat(c.x, ",").concat(c.y, `
  `);
  if (a > 0) {
    var h = tt(r, n, a, o), y = tt(r, n, a, s);
    d += "L ".concat(y.x, ",").concat(y.y, `
            A `).concat(a, ",").concat(a, `,0,
            `).concat(+(Math.abs(l) > 180), ",").concat(+(o <= s), `,
            `).concat(h.x, ",").concat(h.y, " Z");
  } else
    d += "L ".concat(r, ",").concat(n, " Z");
  return d;
}, DD = function(t) {
  var r = t.cx, n = t.cy, a = t.innerRadius, i = t.outerRadius, o = t.cornerRadius, u = t.forceCornerRadius, l = t.cornerIsExternal, s = t.startAngle, f = t.endAngle, c = Dt(f - s), d = ao({
    cx: r,
    cy: n,
    radius: i,
    angle: s,
    sign: c,
    cornerRadius: o,
    cornerIsExternal: l
  }), h = d.circleTangency, y = d.lineTangency, v = d.theta, p = ao({
    cx: r,
    cy: n,
    radius: i,
    angle: f,
    sign: -c,
    cornerRadius: o,
    cornerIsExternal: l
  }), g = p.circleTangency, b = p.lineTangency, w = p.theta, _ = l ? Math.abs(s - f) : Math.abs(s - f) - v - w;
  if (_ < 0)
    return u ? "M ".concat(y.x, ",").concat(y.y, `
        a`).concat(o, ",").concat(o, ",0,0,1,").concat(o * 2, `,0
        a`).concat(o, ",").concat(o, ",0,0,1,").concat(-o * 2, `,0
      `) : y_({
      cx: r,
      cy: n,
      innerRadius: a,
      outerRadius: i,
      startAngle: s,
      endAngle: f
    });
  var m = "M ".concat(y.x, ",").concat(y.y, `
    A`).concat(o, ",").concat(o, ",0,0,").concat(+(c < 0), ",").concat(h.x, ",").concat(h.y, `
    A`).concat(i, ",").concat(i, ",0,").concat(+(_ > 180), ",").concat(+(c < 0), ",").concat(g.x, ",").concat(g.y, `
    A`).concat(o, ",").concat(o, ",0,0,").concat(+(c < 0), ",").concat(b.x, ",").concat(b.y, `
  `);
  if (a > 0) {
    var O = ao({
      cx: r,
      cy: n,
      radius: a,
      angle: s,
      sign: c,
      isExternal: !0,
      cornerRadius: o,
      cornerIsExternal: l
    }), x = O.circleTangency, S = O.lineTangency, T = O.theta, C = ao({
      cx: r,
      cy: n,
      radius: a,
      angle: f,
      sign: -c,
      isExternal: !0,
      cornerRadius: o,
      cornerIsExternal: l
    }), A = C.circleTangency, N = C.lineTangency, $ = C.theta, D = l ? Math.abs(s - f) : Math.abs(s - f) - T - $;
    if (D < 0 && o === 0)
      return "".concat(m, "L").concat(r, ",").concat(n, "Z");
    m += "L".concat(N.x, ",").concat(N.y, `
      A`).concat(o, ",").concat(o, ",0,0,").concat(+(c < 0), ",").concat(A.x, ",").concat(A.y, `
      A`).concat(a, ",").concat(a, ",0,").concat(+(D > 180), ",").concat(+(c > 0), ",").concat(x.x, ",").concat(x.y, `
      A`).concat(o, ",").concat(o, ",0,0,").concat(+(c < 0), ",").concat(S.x, ",").concat(S.y, "Z");
  } else
    m += "L".concat(r, ",").concat(n, "Z");
  return m;
}, LD = {
  cx: 0,
  cy: 0,
  innerRadius: 0,
  outerRadius: 0,
  startAngle: 0,
  endAngle: 0,
  cornerRadius: 0,
  forceCornerRadius: !1,
  cornerIsExternal: !1
}, m_ = function(t) {
  var r = Db(Db({}, LD), t), n = r.cx, a = r.cy, i = r.innerRadius, o = r.outerRadius, u = r.cornerRadius, l = r.forceCornerRadius, s = r.cornerIsExternal, f = r.startAngle, c = r.endAngle, d = r.className;
  if (o < i || f === c)
    return null;
  var h = _e("recharts-sector", d), y = o - i, v = on(u, y, 0, !0), p;
  return v > 0 && Math.abs(f - c) < 360 ? p = DD({
    cx: n,
    cy: a,
    innerRadius: i,
    outerRadius: o,
    cornerRadius: Math.min(v, y / 2),
    forceCornerRadius: l,
    cornerIsExternal: s,
    startAngle: f,
    endAngle: c
  }) : p = y_({
    cx: n,
    cy: a,
    innerRadius: i,
    outerRadius: o,
    startAngle: f,
    endAngle: c
  }), /* @__PURE__ */ M.createElement("path", Kd({}, pe(r, !0), {
    className: h,
    d: p,
    role: "img"
  }));
};
function di(e) {
  "@babel/helpers - typeof";
  return di = typeof Symbol == "function" && typeof Symbol.iterator == "symbol" ? function(t) {
    return typeof t;
  } : function(t) {
    return t && typeof Symbol == "function" && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t;
  }, di(e);
}
function Vd() {
  return Vd = Object.assign ? Object.assign.bind() : function(e) {
    for (var t = 1; t < arguments.length; t++) {
      var r = arguments[t];
      for (var n in r)
        Object.prototype.hasOwnProperty.call(r, n) && (e[n] = r[n]);
    }
    return e;
  }, Vd.apply(this, arguments);
}
function Lb(e, t) {
  var r = Object.keys(e);
  if (Object.getOwnPropertySymbols) {
    var n = Object.getOwnPropertySymbols(e);
    t && (n = n.filter(function(a) {
      return Object.getOwnPropertyDescriptor(e, a).enumerable;
    })), r.push.apply(r, n);
  }
  return r;
}
function qb(e) {
  for (var t = 1; t < arguments.length; t++) {
    var r = arguments[t] != null ? arguments[t] : {};
    t % 2 ? Lb(Object(r), !0).forEach(function(n) {
      qD(e, n, r[n]);
    }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(r)) : Lb(Object(r)).forEach(function(n) {
      Object.defineProperty(e, n, Object.getOwnPropertyDescriptor(r, n));
    });
  }
  return e;
}
function qD(e, t, r) {
  return t = BD(t), t in e ? Object.defineProperty(e, t, { value: r, enumerable: !0, configurable: !0, writable: !0 }) : e[t] = r, e;
}
function BD(e) {
  var t = FD(e, "string");
  return di(t) == "symbol" ? t : t + "";
}
function FD(e, t) {
  if (di(e) != "object" || !e) return e;
  var r = e[Symbol.toPrimitive];
  if (r !== void 0) {
    var n = r.call(e, t);
    if (di(n) != "object") return n;
    throw new TypeError("@@toPrimitive must return a primitive value.");
  }
  return (t === "string" ? String : Number)(e);
}
var Bb = {
  curveBasisClosed: uM,
  curveBasisOpen: lM,
  curveBasis: oM,
  curveBumpX: GT,
  curveBumpY: KT,
  curveLinearClosed: sM,
  curveLinear: Su,
  curveMonotoneX: cM,
  curveMonotoneY: fM,
  curveNatural: dM,
  curveStep: hM,
  curveStepAfter: vM,
  curveStepBefore: pM
}, io = function(t) {
  return t.x === +t.x && t.y === +t.y;
}, Sa = function(t) {
  return t.x;
}, Pa = function(t) {
  return t.y;
}, zD = function(t, r) {
  if (fe(t))
    return t;
  var n = "curve".concat(Ou(t));
  return (n === "curveMonotone" || n === "curveBump") && r ? Bb["".concat(n).concat(r === "vertical" ? "Y" : "X")] : Bb[n] || Su;
}, UD = function(t) {
  var r = t.type, n = r === void 0 ? "linear" : r, a = t.points, i = a === void 0 ? [] : a, o = t.baseLine, u = t.layout, l = t.connectNulls, s = l === void 0 ? !1 : l, f = zD(n, u), c = s ? i.filter(function(v) {
    return io(v);
  }) : i, d;
  if (Array.isArray(o)) {
    var h = s ? o.filter(function(v) {
      return io(v);
    }) : o, y = c.map(function(v, p) {
      return qb(qb({}, v), {}, {
        base: h[p]
      });
    });
    return u === "vertical" ? d = Yi().y(Pa).x1(Sa).x0(function(v) {
      return v.base.x;
    }) : d = Yi().x(Sa).y1(Pa).y0(function(v) {
      return v.base.y;
    }), d.defined(io).curve(f), d(y);
  }
  return u === "vertical" && H(o) ? d = Yi().y(Pa).x1(Sa).x0(o) : H(o) ? d = Yi().x(Sa).y1(Pa).y0(o) : d = ww().x(Sa).y(Pa), d.defined(io).curve(f), d(c);
}, La = function(t) {
  var r = t.className, n = t.points, a = t.path, i = t.pathRef;
  if ((!n || !n.length) && !a)
    return null;
  var o = n && n.length ? UD(t) : a;
  return /* @__PURE__ */ M.createElement("path", Vd({}, pe(t, !1), mo(t), {
    className: _e("recharts-curve", r),
    d: o,
    ref: i
  }));
}, jf = { exports: {} }, Nf, Fb;
function WD() {
  if (Fb) return Nf;
  Fb = 1;
  var e = "SECRET_DO_NOT_PASS_THIS_OR_YOU_WILL_BE_FIRED";
  return Nf = e, Nf;
}
var Cf, zb;
function HD() {
  if (zb) return Cf;
  zb = 1;
  var e = /* @__PURE__ */ WD();
  function t() {
  }
  function r() {
  }
  return r.resetWarningCache = t, Cf = function() {
    function n(o, u, l, s, f, c) {
      if (c !== e) {
        var d = new Error(
          "Calling PropTypes validators directly is not supported by the `prop-types` package. Use PropTypes.checkPropTypes() to call them. Read more at http://fb.me/use-check-prop-types"
        );
        throw d.name = "Invariant Violation", d;
      }
    }
    n.isRequired = n;
    function a() {
      return n;
    }
    var i = {
      array: n,
      bigint: n,
      bool: n,
      func: n,
      number: n,
      object: n,
      string: n,
      symbol: n,
      any: n,
      arrayOf: a,
      element: n,
      elementType: n,
      instanceOf: a,
      node: n,
      objectOf: a,
      oneOf: a,
      oneOfType: a,
      shape: a,
      exact: a,
      checkPropTypes: r,
      resetWarningCache: t
    };
    return i.PropTypes = i, i;
  }, Cf;
}
var Ub;
function GD() {
  return Ub || (Ub = 1, jf.exports = /* @__PURE__ */ HD()()), jf.exports;
}
var KD = /* @__PURE__ */ GD();
const Ae = /* @__PURE__ */ $e(KD);
var VD = Object.getOwnPropertyNames, XD = Object.getOwnPropertySymbols, YD = Object.prototype.hasOwnProperty;
function Wb(e, t) {
  return function(n, a, i) {
    return e(n, a, i) && t(n, a, i);
  };
}
function oo(e) {
  return function(r, n, a) {
    if (!r || !n || typeof r != "object" || typeof n != "object")
      return e(r, n, a);
    var i = a.cache, o = i.get(r), u = i.get(n);
    if (o && u)
      return o === n && u === r;
    i.set(r, n), i.set(n, r);
    var l = e(r, n, a);
    return i.delete(r), i.delete(n), l;
  };
}
function ZD(e) {
  return e?.[Symbol.toStringTag];
}
function Hb(e) {
  return VD(e).concat(XD(e));
}
var JD = Object.hasOwn || (function(e, t) {
  return YD.call(e, t);
});
function gn(e, t) {
  return e === t || !e && !t && e !== e && t !== t;
}
var QD = "__v", eL = "__o", tL = "_owner", Gb = Object.getOwnPropertyDescriptor, Kb = Object.keys;
function rL(e, t, r) {
  var n = e.length;
  if (t.length !== n)
    return !1;
  for (; n-- > 0; )
    if (!r.equals(e[n], t[n], n, n, e, t, r))
      return !1;
  return !0;
}
function nL(e, t) {
  return gn(e.getTime(), t.getTime());
}
function aL(e, t) {
  return e.name === t.name && e.message === t.message && e.cause === t.cause && e.stack === t.stack;
}
function iL(e, t) {
  return e === t;
}
function Vb(e, t, r) {
  var n = e.size;
  if (n !== t.size)
    return !1;
  if (!n)
    return !0;
  for (var a = new Array(n), i = e.entries(), o, u, l = 0; (o = i.next()) && !o.done; ) {
    for (var s = t.entries(), f = !1, c = 0; (u = s.next()) && !u.done; ) {
      if (a[c]) {
        c++;
        continue;
      }
      var d = o.value, h = u.value;
      if (r.equals(d[0], h[0], l, c, e, t, r) && r.equals(d[1], h[1], d[0], h[0], e, t, r)) {
        f = a[c] = !0;
        break;
      }
      c++;
    }
    if (!f)
      return !1;
    l++;
  }
  return !0;
}
var oL = gn;
function uL(e, t, r) {
  var n = Kb(e), a = n.length;
  if (Kb(t).length !== a)
    return !1;
  for (; a-- > 0; )
    if (!g_(e, t, r, n[a]))
      return !1;
  return !0;
}
function Aa(e, t, r) {
  var n = Hb(e), a = n.length;
  if (Hb(t).length !== a)
    return !1;
  for (var i, o, u; a-- > 0; )
    if (i = n[a], !g_(e, t, r, i) || (o = Gb(e, i), u = Gb(t, i), (o || u) && (!o || !u || o.configurable !== u.configurable || o.enumerable !== u.enumerable || o.writable !== u.writable)))
      return !1;
  return !0;
}
function lL(e, t) {
  return gn(e.valueOf(), t.valueOf());
}
function sL(e, t) {
  return e.source === t.source && e.flags === t.flags;
}
function Xb(e, t, r) {
  var n = e.size;
  if (n !== t.size)
    return !1;
  if (!n)
    return !0;
  for (var a = new Array(n), i = e.values(), o, u; (o = i.next()) && !o.done; ) {
    for (var l = t.values(), s = !1, f = 0; (u = l.next()) && !u.done; ) {
      if (!a[f] && r.equals(o.value, u.value, o.value, u.value, e, t, r)) {
        s = a[f] = !0;
        break;
      }
      f++;
    }
    if (!s)
      return !1;
  }
  return !0;
}
function cL(e, t) {
  var r = e.length;
  if (t.length !== r)
    return !1;
  for (; r-- > 0; )
    if (e[r] !== t[r])
      return !1;
  return !0;
}
function fL(e, t) {
  return e.hostname === t.hostname && e.pathname === t.pathname && e.protocol === t.protocol && e.port === t.port && e.hash === t.hash && e.username === t.username && e.password === t.password;
}
function g_(e, t, r, n) {
  return (n === tL || n === eL || n === QD) && (e.$$typeof || t.$$typeof) ? !0 : JD(t, n) && r.equals(e[n], t[n], n, n, e, t, r);
}
var dL = "[object Arguments]", hL = "[object Boolean]", pL = "[object Date]", vL = "[object Error]", yL = "[object Map]", mL = "[object Number]", gL = "[object Object]", bL = "[object RegExp]", xL = "[object Set]", wL = "[object String]", OL = "[object URL]", _L = Array.isArray, Yb = typeof ArrayBuffer == "function" && ArrayBuffer.isView ? ArrayBuffer.isView : null, Zb = Object.assign, SL = Object.prototype.toString.call.bind(Object.prototype.toString);
function PL(e) {
  var t = e.areArraysEqual, r = e.areDatesEqual, n = e.areErrorsEqual, a = e.areFunctionsEqual, i = e.areMapsEqual, o = e.areNumbersEqual, u = e.areObjectsEqual, l = e.arePrimitiveWrappersEqual, s = e.areRegExpsEqual, f = e.areSetsEqual, c = e.areTypedArraysEqual, d = e.areUrlsEqual, h = e.unknownTagComparators;
  return function(v, p, g) {
    if (v === p)
      return !0;
    if (v == null || p == null)
      return !1;
    var b = typeof v;
    if (b !== typeof p)
      return !1;
    if (b !== "object")
      return b === "number" ? o(v, p, g) : b === "function" ? a(v, p, g) : !1;
    var w = v.constructor;
    if (w !== p.constructor)
      return !1;
    if (w === Object)
      return u(v, p, g);
    if (_L(v))
      return t(v, p, g);
    if (Yb != null && Yb(v))
      return c(v, p, g);
    if (w === Date)
      return r(v, p, g);
    if (w === RegExp)
      return s(v, p, g);
    if (w === Map)
      return i(v, p, g);
    if (w === Set)
      return f(v, p, g);
    var _ = SL(v);
    if (_ === pL)
      return r(v, p, g);
    if (_ === bL)
      return s(v, p, g);
    if (_ === yL)
      return i(v, p, g);
    if (_ === xL)
      return f(v, p, g);
    if (_ === gL)
      return typeof v.then != "function" && typeof p.then != "function" && u(v, p, g);
    if (_ === OL)
      return d(v, p, g);
    if (_ === vL)
      return n(v, p, g);
    if (_ === dL)
      return u(v, p, g);
    if (_ === hL || _ === mL || _ === wL)
      return l(v, p, g);
    if (h) {
      var m = h[_];
      if (!m) {
        var O = ZD(v);
        O && (m = h[O]);
      }
      if (m)
        return m(v, p, g);
    }
    return !1;
  };
}
function AL(e) {
  var t = e.circular, r = e.createCustomConfig, n = e.strict, a = {
    areArraysEqual: n ? Aa : rL,
    areDatesEqual: nL,
    areErrorsEqual: aL,
    areFunctionsEqual: iL,
    areMapsEqual: n ? Wb(Vb, Aa) : Vb,
    areNumbersEqual: oL,
    areObjectsEqual: n ? Aa : uL,
    arePrimitiveWrappersEqual: lL,
    areRegExpsEqual: sL,
    areSetsEqual: n ? Wb(Xb, Aa) : Xb,
    areTypedArraysEqual: n ? Aa : cL,
    areUrlsEqual: fL,
    unknownTagComparators: void 0
  };
  if (r && (a = Zb({}, a, r(a))), t) {
    var i = oo(a.areArraysEqual), o = oo(a.areMapsEqual), u = oo(a.areObjectsEqual), l = oo(a.areSetsEqual);
    a = Zb({}, a, {
      areArraysEqual: i,
      areMapsEqual: o,
      areObjectsEqual: u,
      areSetsEqual: l
    });
  }
  return a;
}
function EL(e) {
  return function(t, r, n, a, i, o, u) {
    return e(t, r, u);
  };
}
function TL(e) {
  var t = e.circular, r = e.comparator, n = e.createState, a = e.equals, i = e.strict;
  if (n)
    return function(l, s) {
      var f = n(), c = f.cache, d = c === void 0 ? t ? /* @__PURE__ */ new WeakMap() : void 0 : c, h = f.meta;
      return r(l, s, {
        cache: d,
        equals: a,
        meta: h,
        strict: i
      });
    };
  if (t)
    return function(l, s) {
      return r(l, s, {
        cache: /* @__PURE__ */ new WeakMap(),
        equals: a,
        meta: void 0,
        strict: i
      });
    };
  var o = {
    cache: void 0,
    equals: a,
    meta: void 0,
    strict: i
  };
  return function(l, s) {
    return r(l, s, o);
  };
}
var ML = zr();
zr({ strict: !0 });
zr({ circular: !0 });
zr({
  circular: !0,
  strict: !0
});
zr({
  createInternalComparator: function() {
    return gn;
  }
});
zr({
  strict: !0,
  createInternalComparator: function() {
    return gn;
  }
});
zr({
  circular: !0,
  createInternalComparator: function() {
    return gn;
  }
});
zr({
  circular: !0,
  createInternalComparator: function() {
    return gn;
  },
  strict: !0
});
function zr(e) {
  e === void 0 && (e = {});
  var t = e.circular, r = t === void 0 ? !1 : t, n = e.createInternalComparator, a = e.createState, i = e.strict, o = i === void 0 ? !1 : i, u = AL(e), l = PL(u), s = n ? n(l) : EL(l);
  return TL({ circular: r, comparator: l, createState: a, equals: s, strict: o });
}
function jL(e) {
  typeof requestAnimationFrame < "u" && requestAnimationFrame(e);
}
function Jb(e) {
  var t = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : 0, r = -1, n = function a(i) {
    r < 0 && (r = i), i - r > t ? (e(i), r = -1) : jL(a);
  };
  requestAnimationFrame(n);
}
function Xd(e) {
  "@babel/helpers - typeof";
  return Xd = typeof Symbol == "function" && typeof Symbol.iterator == "symbol" ? function(t) {
    return typeof t;
  } : function(t) {
    return t && typeof Symbol == "function" && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t;
  }, Xd(e);
}
function NL(e) {
  return kL(e) || RL(e) || $L(e) || CL();
}
function CL() {
  throw new TypeError(`Invalid attempt to destructure non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`);
}
function $L(e, t) {
  if (e) {
    if (typeof e == "string") return Qb(e, t);
    var r = Object.prototype.toString.call(e).slice(8, -1);
    if (r === "Object" && e.constructor && (r = e.constructor.name), r === "Map" || r === "Set") return Array.from(e);
    if (r === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r)) return Qb(e, t);
  }
}
function Qb(e, t) {
  (t == null || t > e.length) && (t = e.length);
  for (var r = 0, n = new Array(t); r < t; r++) n[r] = e[r];
  return n;
}
function RL(e) {
  if (typeof Symbol < "u" && e[Symbol.iterator] != null || e["@@iterator"] != null) return Array.from(e);
}
function kL(e) {
  if (Array.isArray(e)) return e;
}
function IL() {
  var e = {}, t = function() {
    return null;
  }, r = !1, n = function a(i) {
    if (!r) {
      if (Array.isArray(i)) {
        if (!i.length)
          return;
        var o = i, u = NL(o), l = u[0], s = u.slice(1);
        if (typeof l == "number") {
          Jb(a.bind(null, s), l);
          return;
        }
        a(l), Jb(a.bind(null, s));
        return;
      }
      Xd(i) === "object" && (e = i, t(e)), typeof i == "function" && i();
    }
  };
  return {
    stop: function() {
      r = !0;
    },
    start: function(i) {
      r = !1, n(i);
    },
    subscribe: function(i) {
      return t = i, function() {
        t = function() {
          return null;
        };
      };
    }
  };
}
function hi(e) {
  "@babel/helpers - typeof";
  return hi = typeof Symbol == "function" && typeof Symbol.iterator == "symbol" ? function(t) {
    return typeof t;
  } : function(t) {
    return t && typeof Symbol == "function" && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t;
  }, hi(e);
}
function ex(e, t) {
  var r = Object.keys(e);
  if (Object.getOwnPropertySymbols) {
    var n = Object.getOwnPropertySymbols(e);
    t && (n = n.filter(function(a) {
      return Object.getOwnPropertyDescriptor(e, a).enumerable;
    })), r.push.apply(r, n);
  }
  return r;
}
function tx(e) {
  for (var t = 1; t < arguments.length; t++) {
    var r = arguments[t] != null ? arguments[t] : {};
    t % 2 ? ex(Object(r), !0).forEach(function(n) {
      b_(e, n, r[n]);
    }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(r)) : ex(Object(r)).forEach(function(n) {
      Object.defineProperty(e, n, Object.getOwnPropertyDescriptor(r, n));
    });
  }
  return e;
}
function b_(e, t, r) {
  return t = DL(t), t in e ? Object.defineProperty(e, t, { value: r, enumerable: !0, configurable: !0, writable: !0 }) : e[t] = r, e;
}
function DL(e) {
  var t = LL(e, "string");
  return hi(t) === "symbol" ? t : String(t);
}
function LL(e, t) {
  if (hi(e) !== "object" || e === null) return e;
  var r = e[Symbol.toPrimitive];
  if (r !== void 0) {
    var n = r.call(e, t);
    if (hi(n) !== "object") return n;
    throw new TypeError("@@toPrimitive must return a primitive value.");
  }
  return (t === "string" ? String : Number)(e);
}
var qL = function(t, r) {
  return [Object.keys(t), Object.keys(r)].reduce(function(n, a) {
    return n.filter(function(i) {
      return a.includes(i);
    });
  });
}, BL = function(t) {
  return t;
}, FL = function(t) {
  return t.replace(/([A-Z])/g, function(r) {
    return "-".concat(r.toLowerCase());
  });
}, qa = function(t, r) {
  return Object.keys(r).reduce(function(n, a) {
    return tx(tx({}, n), {}, b_({}, a, t(a, r[a])));
  }, {});
}, rx = function(t, r, n) {
  return t.map(function(a) {
    return "".concat(FL(a), " ").concat(r, "ms ").concat(n);
  }).join(",");
};
function zL(e, t) {
  return HL(e) || WL(e, t) || x_(e, t) || UL();
}
function UL() {
  throw new TypeError(`Invalid attempt to destructure non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`);
}
function WL(e, t) {
  var r = e == null ? null : typeof Symbol < "u" && e[Symbol.iterator] || e["@@iterator"];
  if (r != null) {
    var n, a, i, o, u = [], l = !0, s = !1;
    try {
      if (i = (r = r.call(e)).next, t !== 0) for (; !(l = (n = i.call(r)).done) && (u.push(n.value), u.length !== t); l = !0) ;
    } catch (f) {
      s = !0, a = f;
    } finally {
      try {
        if (!l && r.return != null && (o = r.return(), Object(o) !== o)) return;
      } finally {
        if (s) throw a;
      }
    }
    return u;
  }
}
function HL(e) {
  if (Array.isArray(e)) return e;
}
function GL(e) {
  return XL(e) || VL(e) || x_(e) || KL();
}
function KL() {
  throw new TypeError(`Invalid attempt to spread non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`);
}
function x_(e, t) {
  if (e) {
    if (typeof e == "string") return Yd(e, t);
    var r = Object.prototype.toString.call(e).slice(8, -1);
    if (r === "Object" && e.constructor && (r = e.constructor.name), r === "Map" || r === "Set") return Array.from(e);
    if (r === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r)) return Yd(e, t);
  }
}
function VL(e) {
  if (typeof Symbol < "u" && e[Symbol.iterator] != null || e["@@iterator"] != null) return Array.from(e);
}
function XL(e) {
  if (Array.isArray(e)) return Yd(e);
}
function Yd(e, t) {
  (t == null || t > e.length) && (t = e.length);
  for (var r = 0, n = new Array(t); r < t; r++) n[r] = e[r];
  return n;
}
var Go = 1e-4, w_ = function(t, r) {
  return [0, 3 * t, 3 * r - 6 * t, 3 * t - 3 * r + 1];
}, O_ = function(t, r) {
  return t.map(function(n, a) {
    return n * Math.pow(r, a);
  }).reduce(function(n, a) {
    return n + a;
  });
}, nx = function(t, r) {
  return function(n) {
    var a = w_(t, r);
    return O_(a, n);
  };
}, YL = function(t, r) {
  return function(n) {
    var a = w_(t, r), i = [].concat(GL(a.map(function(o, u) {
      return o * u;
    }).slice(1)), [0]);
    return O_(i, n);
  };
}, ax = function() {
  for (var t = arguments.length, r = new Array(t), n = 0; n < t; n++)
    r[n] = arguments[n];
  var a = r[0], i = r[1], o = r[2], u = r[3];
  if (r.length === 1)
    switch (r[0]) {
      case "linear":
        a = 0, i = 0, o = 1, u = 1;
        break;
      case "ease":
        a = 0.25, i = 0.1, o = 0.25, u = 1;
        break;
      case "ease-in":
        a = 0.42, i = 0, o = 1, u = 1;
        break;
      case "ease-out":
        a = 0.42, i = 0, o = 0.58, u = 1;
        break;
      case "ease-in-out":
        a = 0, i = 0, o = 0.58, u = 1;
        break;
      default: {
        var l = r[0].split("(");
        if (l[0] === "cubic-bezier" && l[1].split(")")[0].split(",").length === 4) {
          var s = l[1].split(")")[0].split(",").map(function(p) {
            return parseFloat(p);
          }), f = zL(s, 4);
          a = f[0], i = f[1], o = f[2], u = f[3];
        }
      }
    }
  var c = nx(a, o), d = nx(i, u), h = YL(a, o), y = function(g) {
    return g > 1 ? 1 : g < 0 ? 0 : g;
  }, v = function(g) {
    for (var b = g > 1 ? 1 : g, w = b, _ = 0; _ < 8; ++_) {
      var m = c(w) - b, O = h(w);
      if (Math.abs(m - b) < Go || O < Go)
        return d(w);
      w = y(w - m / O);
    }
    return d(w);
  };
  return v.isStepper = !1, v;
}, ZL = function() {
  var t = arguments.length > 0 && arguments[0] !== void 0 ? arguments[0] : {}, r = t.stiff, n = r === void 0 ? 100 : r, a = t.damping, i = a === void 0 ? 8 : a, o = t.dt, u = o === void 0 ? 17 : o, l = function(f, c, d) {
    var h = -(f - c) * n, y = d * i, v = d + (h - y) * u / 1e3, p = d * u / 1e3 + f;
    return Math.abs(p - c) < Go && Math.abs(v) < Go ? [c, 0] : [p, v];
  };
  return l.isStepper = !0, l.dt = u, l;
}, JL = function() {
  for (var t = arguments.length, r = new Array(t), n = 0; n < t; n++)
    r[n] = arguments[n];
  var a = r[0];
  if (typeof a == "string")
    switch (a) {
      case "ease":
      case "ease-in-out":
      case "ease-out":
      case "ease-in":
      case "linear":
        return ax(a);
      case "spring":
        return ZL();
      default:
        if (a.split("(")[0] === "cubic-bezier")
          return ax(a);
    }
  return typeof a == "function" ? a : null;
};
function pi(e) {
  "@babel/helpers - typeof";
  return pi = typeof Symbol == "function" && typeof Symbol.iterator == "symbol" ? function(t) {
    return typeof t;
  } : function(t) {
    return t && typeof Symbol == "function" && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t;
  }, pi(e);
}
function ix(e) {
  return tq(e) || eq(e) || __(e) || QL();
}
function QL() {
  throw new TypeError(`Invalid attempt to spread non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`);
}
function eq(e) {
  if (typeof Symbol < "u" && e[Symbol.iterator] != null || e["@@iterator"] != null) return Array.from(e);
}
function tq(e) {
  if (Array.isArray(e)) return Jd(e);
}
function ox(e, t) {
  var r = Object.keys(e);
  if (Object.getOwnPropertySymbols) {
    var n = Object.getOwnPropertySymbols(e);
    t && (n = n.filter(function(a) {
      return Object.getOwnPropertyDescriptor(e, a).enumerable;
    })), r.push.apply(r, n);
  }
  return r;
}
function et(e) {
  for (var t = 1; t < arguments.length; t++) {
    var r = arguments[t] != null ? arguments[t] : {};
    t % 2 ? ox(Object(r), !0).forEach(function(n) {
      Zd(e, n, r[n]);
    }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(r)) : ox(Object(r)).forEach(function(n) {
      Object.defineProperty(e, n, Object.getOwnPropertyDescriptor(r, n));
    });
  }
  return e;
}
function Zd(e, t, r) {
  return t = rq(t), t in e ? Object.defineProperty(e, t, { value: r, enumerable: !0, configurable: !0, writable: !0 }) : e[t] = r, e;
}
function rq(e) {
  var t = nq(e, "string");
  return pi(t) === "symbol" ? t : String(t);
}
function nq(e, t) {
  if (pi(e) !== "object" || e === null) return e;
  var r = e[Symbol.toPrimitive];
  if (r !== void 0) {
    var n = r.call(e, t);
    if (pi(n) !== "object") return n;
    throw new TypeError("@@toPrimitive must return a primitive value.");
  }
  return (t === "string" ? String : Number)(e);
}
function aq(e, t) {
  return uq(e) || oq(e, t) || __(e, t) || iq();
}
function iq() {
  throw new TypeError(`Invalid attempt to destructure non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`);
}
function __(e, t) {
  if (e) {
    if (typeof e == "string") return Jd(e, t);
    var r = Object.prototype.toString.call(e).slice(8, -1);
    if (r === "Object" && e.constructor && (r = e.constructor.name), r === "Map" || r === "Set") return Array.from(e);
    if (r === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r)) return Jd(e, t);
  }
}
function Jd(e, t) {
  (t == null || t > e.length) && (t = e.length);
  for (var r = 0, n = new Array(t); r < t; r++) n[r] = e[r];
  return n;
}
function oq(e, t) {
  var r = e == null ? null : typeof Symbol < "u" && e[Symbol.iterator] || e["@@iterator"];
  if (r != null) {
    var n, a, i, o, u = [], l = !0, s = !1;
    try {
      if (i = (r = r.call(e)).next, t !== 0) for (; !(l = (n = i.call(r)).done) && (u.push(n.value), u.length !== t); l = !0) ;
    } catch (f) {
      s = !0, a = f;
    } finally {
      try {
        if (!l && r.return != null && (o = r.return(), Object(o) !== o)) return;
      } finally {
        if (s) throw a;
      }
    }
    return u;
  }
}
function uq(e) {
  if (Array.isArray(e)) return e;
}
var Ko = function(t, r, n) {
  return t + (r - t) * n;
}, Qd = function(t) {
  var r = t.from, n = t.to;
  return r !== n;
}, lq = function e(t, r, n) {
  var a = qa(function(i, o) {
    if (Qd(o)) {
      var u = t(o.from, o.to, o.velocity), l = aq(u, 2), s = l[0], f = l[1];
      return et(et({}, o), {}, {
        from: s,
        velocity: f
      });
    }
    return o;
  }, r);
  return n < 1 ? qa(function(i, o) {
    return Qd(o) ? et(et({}, o), {}, {
      velocity: Ko(o.velocity, a[i].velocity, n),
      from: Ko(o.from, a[i].from, n)
    }) : o;
  }, r) : e(t, a, n - 1);
};
const sq = (function(e, t, r, n, a) {
  var i = qL(e, t), o = i.reduce(function(p, g) {
    return et(et({}, p), {}, Zd({}, g, [e[g], t[g]]));
  }, {}), u = i.reduce(function(p, g) {
    return et(et({}, p), {}, Zd({}, g, {
      from: e[g],
      velocity: 0,
      to: t[g]
    }));
  }, {}), l = -1, s, f, c = function() {
    return null;
  }, d = function() {
    return qa(function(g, b) {
      return b.from;
    }, u);
  }, h = function() {
    return !Object.values(u).filter(Qd).length;
  }, y = function(g) {
    s || (s = g);
    var b = g - s, w = b / r.dt;
    u = lq(r, u, w), a(et(et(et({}, e), t), d())), s = g, h() || (l = requestAnimationFrame(c));
  }, v = function(g) {
    f || (f = g);
    var b = (g - f) / n, w = qa(function(m, O) {
      return Ko.apply(void 0, ix(O).concat([r(b)]));
    }, o);
    if (a(et(et(et({}, e), t), w)), b < 1)
      l = requestAnimationFrame(c);
    else {
      var _ = qa(function(m, O) {
        return Ko.apply(void 0, ix(O).concat([r(1)]));
      }, o);
      a(et(et(et({}, e), t), _));
    }
  };
  return c = r.isStepper ? y : v, function() {
    return requestAnimationFrame(c), function() {
      cancelAnimationFrame(l);
    };
  };
});
function Wn(e) {
  "@babel/helpers - typeof";
  return Wn = typeof Symbol == "function" && typeof Symbol.iterator == "symbol" ? function(t) {
    return typeof t;
  } : function(t) {
    return t && typeof Symbol == "function" && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t;
  }, Wn(e);
}
var cq = ["children", "begin", "duration", "attributeName", "easing", "isActive", "steps", "from", "to", "canBegin", "onAnimationEnd", "shouldReAnimate", "onAnimationReStart"];
function fq(e, t) {
  if (e == null) return {};
  var r = dq(e, t), n, a;
  if (Object.getOwnPropertySymbols) {
    var i = Object.getOwnPropertySymbols(e);
    for (a = 0; a < i.length; a++)
      n = i[a], !(t.indexOf(n) >= 0) && Object.prototype.propertyIsEnumerable.call(e, n) && (r[n] = e[n]);
  }
  return r;
}
function dq(e, t) {
  if (e == null) return {};
  var r = {}, n = Object.keys(e), a, i;
  for (i = 0; i < n.length; i++)
    a = n[i], !(t.indexOf(a) >= 0) && (r[a] = e[a]);
  return r;
}
function $f(e) {
  return yq(e) || vq(e) || pq(e) || hq();
}
function hq() {
  throw new TypeError(`Invalid attempt to spread non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`);
}
function pq(e, t) {
  if (e) {
    if (typeof e == "string") return eh(e, t);
    var r = Object.prototype.toString.call(e).slice(8, -1);
    if (r === "Object" && e.constructor && (r = e.constructor.name), r === "Map" || r === "Set") return Array.from(e);
    if (r === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r)) return eh(e, t);
  }
}
function vq(e) {
  if (typeof Symbol < "u" && e[Symbol.iterator] != null || e["@@iterator"] != null) return Array.from(e);
}
function yq(e) {
  if (Array.isArray(e)) return eh(e);
}
function eh(e, t) {
  (t == null || t > e.length) && (t = e.length);
  for (var r = 0, n = new Array(t); r < t; r++) n[r] = e[r];
  return n;
}
function ux(e, t) {
  var r = Object.keys(e);
  if (Object.getOwnPropertySymbols) {
    var n = Object.getOwnPropertySymbols(e);
    t && (n = n.filter(function(a) {
      return Object.getOwnPropertyDescriptor(e, a).enumerable;
    })), r.push.apply(r, n);
  }
  return r;
}
function $t(e) {
  for (var t = 1; t < arguments.length; t++) {
    var r = arguments[t] != null ? arguments[t] : {};
    t % 2 ? ux(Object(r), !0).forEach(function(n) {
      $a(e, n, r[n]);
    }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(r)) : ux(Object(r)).forEach(function(n) {
      Object.defineProperty(e, n, Object.getOwnPropertyDescriptor(r, n));
    });
  }
  return e;
}
function $a(e, t, r) {
  return t = S_(t), t in e ? Object.defineProperty(e, t, { value: r, enumerable: !0, configurable: !0, writable: !0 }) : e[t] = r, e;
}
function mq(e, t) {
  if (!(e instanceof t))
    throw new TypeError("Cannot call a class as a function");
}
function gq(e, t) {
  for (var r = 0; r < t.length; r++) {
    var n = t[r];
    n.enumerable = n.enumerable || !1, n.configurable = !0, "value" in n && (n.writable = !0), Object.defineProperty(e, S_(n.key), n);
  }
}
function bq(e, t, r) {
  return t && gq(e.prototype, t), Object.defineProperty(e, "prototype", { writable: !1 }), e;
}
function S_(e) {
  var t = xq(e, "string");
  return Wn(t) === "symbol" ? t : String(t);
}
function xq(e, t) {
  if (Wn(e) !== "object" || e === null) return e;
  var r = e[Symbol.toPrimitive];
  if (r !== void 0) {
    var n = r.call(e, t);
    if (Wn(n) !== "object") return n;
    throw new TypeError("@@toPrimitive must return a primitive value.");
  }
  return (t === "string" ? String : Number)(e);
}
function wq(e, t) {
  if (typeof t != "function" && t !== null)
    throw new TypeError("Super expression must either be null or a function");
  e.prototype = Object.create(t && t.prototype, { constructor: { value: e, writable: !0, configurable: !0 } }), Object.defineProperty(e, "prototype", { writable: !1 }), t && th(e, t);
}
function th(e, t) {
  return th = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function(n, a) {
    return n.__proto__ = a, n;
  }, th(e, t);
}
function Oq(e) {
  var t = _q();
  return function() {
    var n = Vo(e), a;
    if (t) {
      var i = Vo(this).constructor;
      a = Reflect.construct(n, arguments, i);
    } else
      a = n.apply(this, arguments);
    return rh(this, a);
  };
}
function rh(e, t) {
  if (t && (Wn(t) === "object" || typeof t == "function"))
    return t;
  if (t !== void 0)
    throw new TypeError("Derived constructors may only return object or undefined");
  return nh(e);
}
function nh(e) {
  if (e === void 0)
    throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
  return e;
}
function _q() {
  if (typeof Reflect > "u" || !Reflect.construct || Reflect.construct.sham) return !1;
  if (typeof Proxy == "function") return !0;
  try {
    return Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function() {
    })), !0;
  } catch {
    return !1;
  }
}
function Vo(e) {
  return Vo = Object.setPrototypeOf ? Object.getPrototypeOf.bind() : function(r) {
    return r.__proto__ || Object.getPrototypeOf(r);
  }, Vo(e);
}
var gr = /* @__PURE__ */ (function(e) {
  wq(r, e);
  var t = Oq(r);
  function r(n, a) {
    var i;
    mq(this, r), i = t.call(this, n, a);
    var o = i.props, u = o.isActive, l = o.attributeName, s = o.from, f = o.to, c = o.steps, d = o.children, h = o.duration;
    if (i.handleStyleChange = i.handleStyleChange.bind(nh(i)), i.changeStyle = i.changeStyle.bind(nh(i)), !u || h <= 0)
      return i.state = {
        style: {}
      }, typeof d == "function" && (i.state = {
        style: f
      }), rh(i);
    if (c && c.length)
      i.state = {
        style: c[0].style
      };
    else if (s) {
      if (typeof d == "function")
        return i.state = {
          style: s
        }, rh(i);
      i.state = {
        style: l ? $a({}, l, s) : s
      };
    } else
      i.state = {
        style: {}
      };
    return i;
  }
  return bq(r, [{
    key: "componentDidMount",
    value: function() {
      var a = this.props, i = a.isActive, o = a.canBegin;
      this.mounted = !0, !(!i || !o) && this.runAnimation(this.props);
    }
  }, {
    key: "componentDidUpdate",
    value: function(a) {
      var i = this.props, o = i.isActive, u = i.canBegin, l = i.attributeName, s = i.shouldReAnimate, f = i.to, c = i.from, d = this.state.style;
      if (u) {
        if (!o) {
          var h = {
            style: l ? $a({}, l, f) : f
          };
          this.state && d && (l && d[l] !== f || !l && d !== f) && this.setState(h);
          return;
        }
        if (!(ML(a.to, f) && a.canBegin && a.isActive)) {
          var y = !a.canBegin || !a.isActive;
          this.manager && this.manager.stop(), this.stopJSAnimation && this.stopJSAnimation();
          var v = y || s ? c : a.to;
          if (this.state && d) {
            var p = {
              style: l ? $a({}, l, v) : v
            };
            (l && d[l] !== v || !l && d !== v) && this.setState(p);
          }
          this.runAnimation($t($t({}, this.props), {}, {
            from: v,
            begin: 0
          }));
        }
      }
    }
  }, {
    key: "componentWillUnmount",
    value: function() {
      this.mounted = !1;
      var a = this.props.onAnimationEnd;
      this.unSubscribe && this.unSubscribe(), this.manager && (this.manager.stop(), this.manager = null), this.stopJSAnimation && this.stopJSAnimation(), a && a();
    }
  }, {
    key: "handleStyleChange",
    value: function(a) {
      this.changeStyle(a);
    }
  }, {
    key: "changeStyle",
    value: function(a) {
      this.mounted && this.setState({
        style: a
      });
    }
  }, {
    key: "runJSAnimation",
    value: function(a) {
      var i = this, o = a.from, u = a.to, l = a.duration, s = a.easing, f = a.begin, c = a.onAnimationEnd, d = a.onAnimationStart, h = sq(o, u, JL(s), l, this.changeStyle), y = function() {
        i.stopJSAnimation = h();
      };
      this.manager.start([d, f, y, l, c]);
    }
  }, {
    key: "runStepAnimation",
    value: function(a) {
      var i = this, o = a.steps, u = a.begin, l = a.onAnimationStart, s = o[0], f = s.style, c = s.duration, d = c === void 0 ? 0 : c, h = function(v, p, g) {
        if (g === 0)
          return v;
        var b = p.duration, w = p.easing, _ = w === void 0 ? "ease" : w, m = p.style, O = p.properties, x = p.onAnimationEnd, S = g > 0 ? o[g - 1] : p, T = O || Object.keys(m);
        if (typeof _ == "function" || _ === "spring")
          return [].concat($f(v), [i.runJSAnimation.bind(i, {
            from: S.style,
            to: m,
            duration: b,
            easing: _
          }), b]);
        var C = rx(T, b, _), A = $t($t($t({}, S.style), m), {}, {
          transition: C
        });
        return [].concat($f(v), [A, b, x]).filter(BL);
      };
      return this.manager.start([l].concat($f(o.reduce(h, [f, Math.max(d, u)])), [a.onAnimationEnd]));
    }
  }, {
    key: "runAnimation",
    value: function(a) {
      this.manager || (this.manager = IL());
      var i = a.begin, o = a.duration, u = a.attributeName, l = a.to, s = a.easing, f = a.onAnimationStart, c = a.onAnimationEnd, d = a.steps, h = a.children, y = this.manager;
      if (this.unSubscribe = y.subscribe(this.handleStyleChange), typeof s == "function" || typeof h == "function" || s === "spring") {
        this.runJSAnimation(a);
        return;
      }
      if (d.length > 1) {
        this.runStepAnimation(a);
        return;
      }
      var v = u ? $a({}, u, l) : l, p = rx(Object.keys(v), o, s);
      y.start([f, i, $t($t({}, v), {}, {
        transition: p
      }), o, c]);
    }
  }, {
    key: "render",
    value: function() {
      var a = this.props, i = a.children;
      a.begin;
      var o = a.duration;
      a.attributeName, a.easing;
      var u = a.isActive;
      a.steps, a.from, a.to, a.canBegin, a.onAnimationEnd, a.shouldReAnimate, a.onAnimationReStart;
      var l = fq(a, cq), s = $r.count(i), f = this.state.style;
      if (typeof i == "function")
        return i(f);
      if (!u || s === 0 || o <= 0)
        return i;
      var c = function(h) {
        var y = h.props, v = y.style, p = v === void 0 ? {} : v, g = y.className, b = /* @__PURE__ */ Ue(h, $t($t({}, l), {}, {
          style: $t($t({}, p), f),
          className: g
        }));
        return b;
      };
      return s === 1 ? c($r.only(i)) : /* @__PURE__ */ M.createElement("div", null, $r.map(i, function(d) {
        return c(d);
      }));
    }
  }]), r;
})(br);
gr.displayName = "Animate";
gr.defaultProps = {
  begin: 0,
  duration: 1e3,
  from: "",
  to: "",
  attributeName: "",
  easing: "ease",
  isActive: !0,
  canBegin: !0,
  steps: [],
  onAnimationEnd: function() {
  },
  onAnimationStart: function() {
  }
};
gr.propTypes = {
  from: Ae.oneOfType([Ae.object, Ae.string]),
  to: Ae.oneOfType([Ae.object, Ae.string]),
  attributeName: Ae.string,
  // animation duration
  duration: Ae.number,
  begin: Ae.number,
  easing: Ae.oneOfType([Ae.string, Ae.func]),
  steps: Ae.arrayOf(Ae.shape({
    duration: Ae.number.isRequired,
    style: Ae.object.isRequired,
    easing: Ae.oneOfType([Ae.oneOf(["ease", "ease-in", "ease-out", "ease-in-out", "linear"]), Ae.func]),
    // transition css properties(dash case), optional
    properties: Ae.arrayOf("string"),
    onAnimationEnd: Ae.func
  })),
  children: Ae.oneOfType([Ae.node, Ae.func]),
  isActive: Ae.bool,
  canBegin: Ae.bool,
  onAnimationEnd: Ae.func,
  // decide if it should reanimate with initial from style when props change
  shouldReAnimate: Ae.bool,
  onAnimationStart: Ae.func,
  onAnimationReStart: Ae.func
};
function vi(e) {
  "@babel/helpers - typeof";
  return vi = typeof Symbol == "function" && typeof Symbol.iterator == "symbol" ? function(t) {
    return typeof t;
  } : function(t) {
    return t && typeof Symbol == "function" && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t;
  }, vi(e);
}
function Xo() {
  return Xo = Object.assign ? Object.assign.bind() : function(e) {
    for (var t = 1; t < arguments.length; t++) {
      var r = arguments[t];
      for (var n in r)
        Object.prototype.hasOwnProperty.call(r, n) && (e[n] = r[n]);
    }
    return e;
  }, Xo.apply(this, arguments);
}
function Sq(e, t) {
  return Tq(e) || Eq(e, t) || Aq(e, t) || Pq();
}
function Pq() {
  throw new TypeError(`Invalid attempt to destructure non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`);
}
function Aq(e, t) {
  if (e) {
    if (typeof e == "string") return lx(e, t);
    var r = Object.prototype.toString.call(e).slice(8, -1);
    if (r === "Object" && e.constructor && (r = e.constructor.name), r === "Map" || r === "Set") return Array.from(e);
    if (r === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r)) return lx(e, t);
  }
}
function lx(e, t) {
  (t == null || t > e.length) && (t = e.length);
  for (var r = 0, n = new Array(t); r < t; r++) n[r] = e[r];
  return n;
}
function Eq(e, t) {
  var r = e == null ? null : typeof Symbol < "u" && e[Symbol.iterator] || e["@@iterator"];
  if (r != null) {
    var n, a, i, o, u = [], l = !0, s = !1;
    try {
      if (i = (r = r.call(e)).next, t !== 0) for (; !(l = (n = i.call(r)).done) && (u.push(n.value), u.length !== t); l = !0) ;
    } catch (f) {
      s = !0, a = f;
    } finally {
      try {
        if (!l && r.return != null && (o = r.return(), Object(o) !== o)) return;
      } finally {
        if (s) throw a;
      }
    }
    return u;
  }
}
function Tq(e) {
  if (Array.isArray(e)) return e;
}
function sx(e, t) {
  var r = Object.keys(e);
  if (Object.getOwnPropertySymbols) {
    var n = Object.getOwnPropertySymbols(e);
    t && (n = n.filter(function(a) {
      return Object.getOwnPropertyDescriptor(e, a).enumerable;
    })), r.push.apply(r, n);
  }
  return r;
}
function cx(e) {
  for (var t = 1; t < arguments.length; t++) {
    var r = arguments[t] != null ? arguments[t] : {};
    t % 2 ? sx(Object(r), !0).forEach(function(n) {
      Mq(e, n, r[n]);
    }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(r)) : sx(Object(r)).forEach(function(n) {
      Object.defineProperty(e, n, Object.getOwnPropertyDescriptor(r, n));
    });
  }
  return e;
}
function Mq(e, t, r) {
  return t = jq(t), t in e ? Object.defineProperty(e, t, { value: r, enumerable: !0, configurable: !0, writable: !0 }) : e[t] = r, e;
}
function jq(e) {
  var t = Nq(e, "string");
  return vi(t) == "symbol" ? t : t + "";
}
function Nq(e, t) {
  if (vi(e) != "object" || !e) return e;
  var r = e[Symbol.toPrimitive];
  if (r !== void 0) {
    var n = r.call(e, t);
    if (vi(n) != "object") return n;
    throw new TypeError("@@toPrimitive must return a primitive value.");
  }
  return (t === "string" ? String : Number)(e);
}
var fx = function(t, r, n, a, i) {
  var o = Math.min(Math.abs(n) / 2, Math.abs(a) / 2), u = a >= 0 ? 1 : -1, l = n >= 0 ? 1 : -1, s = a >= 0 && n >= 0 || a < 0 && n < 0 ? 1 : 0, f;
  if (o > 0 && i instanceof Array) {
    for (var c = [0, 0, 0, 0], d = 0, h = 4; d < h; d++)
      c[d] = i[d] > o ? o : i[d];
    f = "M".concat(t, ",").concat(r + u * c[0]), c[0] > 0 && (f += "A ".concat(c[0], ",").concat(c[0], ",0,0,").concat(s, ",").concat(t + l * c[0], ",").concat(r)), f += "L ".concat(t + n - l * c[1], ",").concat(r), c[1] > 0 && (f += "A ".concat(c[1], ",").concat(c[1], ",0,0,").concat(s, `,
        `).concat(t + n, ",").concat(r + u * c[1])), f += "L ".concat(t + n, ",").concat(r + a - u * c[2]), c[2] > 0 && (f += "A ".concat(c[2], ",").concat(c[2], ",0,0,").concat(s, `,
        `).concat(t + n - l * c[2], ",").concat(r + a)), f += "L ".concat(t + l * c[3], ",").concat(r + a), c[3] > 0 && (f += "A ".concat(c[3], ",").concat(c[3], ",0,0,").concat(s, `,
        `).concat(t, ",").concat(r + a - u * c[3])), f += "Z";
  } else if (o > 0 && i === +i && i > 0) {
    var y = Math.min(o, i);
    f = "M ".concat(t, ",").concat(r + u * y, `
            A `).concat(y, ",").concat(y, ",0,0,").concat(s, ",").concat(t + l * y, ",").concat(r, `
            L `).concat(t + n - l * y, ",").concat(r, `
            A `).concat(y, ",").concat(y, ",0,0,").concat(s, ",").concat(t + n, ",").concat(r + u * y, `
            L `).concat(t + n, ",").concat(r + a - u * y, `
            A `).concat(y, ",").concat(y, ",0,0,").concat(s, ",").concat(t + n - l * y, ",").concat(r + a, `
            L `).concat(t + l * y, ",").concat(r + a, `
            A `).concat(y, ",").concat(y, ",0,0,").concat(s, ",").concat(t, ",").concat(r + a - u * y, " Z");
  } else
    f = "M ".concat(t, ",").concat(r, " h ").concat(n, " v ").concat(a, " h ").concat(-n, " Z");
  return f;
}, Cq = function(t, r) {
  if (!t || !r)
    return !1;
  var n = t.x, a = t.y, i = r.x, o = r.y, u = r.width, l = r.height;
  if (Math.abs(u) > 0 && Math.abs(l) > 0) {
    var s = Math.min(i, i + u), f = Math.max(i, i + u), c = Math.min(o, o + l), d = Math.max(o, o + l);
    return n >= s && n <= f && a >= c && a <= d;
  }
  return !1;
}, $q = {
  x: 0,
  y: 0,
  width: 0,
  height: 0,
  // The radius of border
  // The radius of four corners when radius is a number
  // The radius of left-top, right-top, right-bottom, left-bottom when radius is an array
  radius: 0,
  isAnimationActive: !1,
  isUpdateAnimationActive: !1,
  animationBegin: 0,
  animationDuration: 1500,
  animationEasing: "ease"
}, kp = function(t) {
  var r = cx(cx({}, $q), t), n = pr(), a = Oe(-1), i = Sq(a, 2), o = i[0], u = i[1];
  It(function() {
    if (n.current && n.current.getTotalLength)
      try {
        var _ = n.current.getTotalLength();
        _ && u(_);
      } catch {
      }
  }, []);
  var l = r.x, s = r.y, f = r.width, c = r.height, d = r.radius, h = r.className, y = r.animationEasing, v = r.animationDuration, p = r.animationBegin, g = r.isAnimationActive, b = r.isUpdateAnimationActive;
  if (l !== +l || s !== +s || f !== +f || c !== +c || f === 0 || c === 0)
    return null;
  var w = _e("recharts-rectangle", h);
  return b ? /* @__PURE__ */ M.createElement(gr, {
    canBegin: o > 0,
    from: {
      width: f,
      height: c,
      x: l,
      y: s
    },
    to: {
      width: f,
      height: c,
      x: l,
      y: s
    },
    duration: v,
    animationEasing: y,
    isActive: b
  }, function(_) {
    var m = _.width, O = _.height, x = _.x, S = _.y;
    return /* @__PURE__ */ M.createElement(gr, {
      canBegin: o > 0,
      from: "0px ".concat(o === -1 ? 1 : o, "px"),
      to: "".concat(o, "px 0px"),
      attributeName: "strokeDasharray",
      begin: p,
      duration: v,
      isActive: g,
      easing: y
    }, /* @__PURE__ */ M.createElement("path", Xo({}, pe(r, !0), {
      className: w,
      d: fx(x, S, m, O, d),
      ref: n
    })));
  }) : /* @__PURE__ */ M.createElement("path", Xo({}, pe(r, !0), {
    className: w,
    d: fx(l, s, f, c, d)
  }));
};
function ah() {
  return ah = Object.assign ? Object.assign.bind() : function(e) {
    for (var t = 1; t < arguments.length; t++) {
      var r = arguments[t];
      for (var n in r)
        Object.prototype.hasOwnProperty.call(r, n) && (e[n] = r[n]);
    }
    return e;
  }, ah.apply(this, arguments);
}
var Ip = function(t) {
  var r = t.cx, n = t.cy, a = t.r, i = t.className, o = _e("recharts-dot", i);
  return r === +r && n === +n && a === +a ? /* @__PURE__ */ M.createElement("circle", ah({}, pe(t, !1), mo(t), {
    className: o,
    cx: r,
    cy: n,
    r: a
  })) : null;
};
function yi(e) {
  "@babel/helpers - typeof";
  return yi = typeof Symbol == "function" && typeof Symbol.iterator == "symbol" ? function(t) {
    return typeof t;
  } : function(t) {
    return t && typeof Symbol == "function" && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t;
  }, yi(e);
}
var Rq = ["x", "y", "top", "left", "width", "height", "className"];
function ih() {
  return ih = Object.assign ? Object.assign.bind() : function(e) {
    for (var t = 1; t < arguments.length; t++) {
      var r = arguments[t];
      for (var n in r)
        Object.prototype.hasOwnProperty.call(r, n) && (e[n] = r[n]);
    }
    return e;
  }, ih.apply(this, arguments);
}
function dx(e, t) {
  var r = Object.keys(e);
  if (Object.getOwnPropertySymbols) {
    var n = Object.getOwnPropertySymbols(e);
    t && (n = n.filter(function(a) {
      return Object.getOwnPropertyDescriptor(e, a).enumerable;
    })), r.push.apply(r, n);
  }
  return r;
}
function kq(e) {
  for (var t = 1; t < arguments.length; t++) {
    var r = arguments[t] != null ? arguments[t] : {};
    t % 2 ? dx(Object(r), !0).forEach(function(n) {
      Iq(e, n, r[n]);
    }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(r)) : dx(Object(r)).forEach(function(n) {
      Object.defineProperty(e, n, Object.getOwnPropertyDescriptor(r, n));
    });
  }
  return e;
}
function Iq(e, t, r) {
  return t = Dq(t), t in e ? Object.defineProperty(e, t, { value: r, enumerable: !0, configurable: !0, writable: !0 }) : e[t] = r, e;
}
function Dq(e) {
  var t = Lq(e, "string");
  return yi(t) == "symbol" ? t : t + "";
}
function Lq(e, t) {
  if (yi(e) != "object" || !e) return e;
  var r = e[Symbol.toPrimitive];
  if (r !== void 0) {
    var n = r.call(e, t);
    if (yi(n) != "object") return n;
    throw new TypeError("@@toPrimitive must return a primitive value.");
  }
  return (t === "string" ? String : Number)(e);
}
function qq(e, t) {
  if (e == null) return {};
  var r = Bq(e, t), n, a;
  if (Object.getOwnPropertySymbols) {
    var i = Object.getOwnPropertySymbols(e);
    for (a = 0; a < i.length; a++)
      n = i[a], !(t.indexOf(n) >= 0) && Object.prototype.propertyIsEnumerable.call(e, n) && (r[n] = e[n]);
  }
  return r;
}
function Bq(e, t) {
  if (e == null) return {};
  var r = {};
  for (var n in e)
    if (Object.prototype.hasOwnProperty.call(e, n)) {
      if (t.indexOf(n) >= 0) continue;
      r[n] = e[n];
    }
  return r;
}
var Fq = function(t, r, n, a, i, o) {
  return "M".concat(t, ",").concat(i, "v").concat(a, "M").concat(o, ",").concat(r, "h").concat(n);
}, zq = function(t) {
  var r = t.x, n = r === void 0 ? 0 : r, a = t.y, i = a === void 0 ? 0 : a, o = t.top, u = o === void 0 ? 0 : o, l = t.left, s = l === void 0 ? 0 : l, f = t.width, c = f === void 0 ? 0 : f, d = t.height, h = d === void 0 ? 0 : d, y = t.className, v = qq(t, Rq), p = kq({
    x: n,
    y: i,
    top: u,
    left: s,
    width: c,
    height: h
  }, v);
  return !H(n) || !H(i) || !H(c) || !H(h) || !H(u) || !H(s) ? null : /* @__PURE__ */ M.createElement("path", ih({}, pe(p, !0), {
    className: _e("recharts-cross", y),
    d: Fq(n, i, c, h, u, s)
  }));
}, Rf, hx;
function Uq() {
  if (hx) return Rf;
  hx = 1;
  var e = Ww(), t = e(Object.getPrototypeOf, Object);
  return Rf = t, Rf;
}
var kf, px;
function Wq() {
  if (px) return kf;
  px = 1;
  var e = xr(), t = Uq(), r = wr(), n = "[object Object]", a = Function.prototype, i = Object.prototype, o = a.toString, u = i.hasOwnProperty, l = o.call(Object);
  function s(f) {
    if (!r(f) || e(f) != n)
      return !1;
    var c = t(f);
    if (c === null)
      return !0;
    var d = u.call(c, "constructor") && c.constructor;
    return typeof d == "function" && d instanceof d && o.call(d) == l;
  }
  return kf = s, kf;
}
var Hq = Wq();
const Gq = /* @__PURE__ */ $e(Hq);
var If, vx;
function Kq() {
  if (vx) return If;
  vx = 1;
  var e = xr(), t = wr(), r = "[object Boolean]";
  function n(a) {
    return a === !0 || a === !1 || t(a) && e(a) == r;
  }
  return If = n, If;
}
var Vq = Kq();
const Xq = /* @__PURE__ */ $e(Vq);
function mi(e) {
  "@babel/helpers - typeof";
  return mi = typeof Symbol == "function" && typeof Symbol.iterator == "symbol" ? function(t) {
    return typeof t;
  } : function(t) {
    return t && typeof Symbol == "function" && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t;
  }, mi(e);
}
function Yo() {
  return Yo = Object.assign ? Object.assign.bind() : function(e) {
    for (var t = 1; t < arguments.length; t++) {
      var r = arguments[t];
      for (var n in r)
        Object.prototype.hasOwnProperty.call(r, n) && (e[n] = r[n]);
    }
    return e;
  }, Yo.apply(this, arguments);
}
function Yq(e, t) {
  return e5(e) || Qq(e, t) || Jq(e, t) || Zq();
}
function Zq() {
  throw new TypeError(`Invalid attempt to destructure non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`);
}
function Jq(e, t) {
  if (e) {
    if (typeof e == "string") return yx(e, t);
    var r = Object.prototype.toString.call(e).slice(8, -1);
    if (r === "Object" && e.constructor && (r = e.constructor.name), r === "Map" || r === "Set") return Array.from(e);
    if (r === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r)) return yx(e, t);
  }
}
function yx(e, t) {
  (t == null || t > e.length) && (t = e.length);
  for (var r = 0, n = new Array(t); r < t; r++) n[r] = e[r];
  return n;
}
function Qq(e, t) {
  var r = e == null ? null : typeof Symbol < "u" && e[Symbol.iterator] || e["@@iterator"];
  if (r != null) {
    var n, a, i, o, u = [], l = !0, s = !1;
    try {
      if (i = (r = r.call(e)).next, t !== 0) for (; !(l = (n = i.call(r)).done) && (u.push(n.value), u.length !== t); l = !0) ;
    } catch (f) {
      s = !0, a = f;
    } finally {
      try {
        if (!l && r.return != null && (o = r.return(), Object(o) !== o)) return;
      } finally {
        if (s) throw a;
      }
    }
    return u;
  }
}
function e5(e) {
  if (Array.isArray(e)) return e;
}
function mx(e, t) {
  var r = Object.keys(e);
  if (Object.getOwnPropertySymbols) {
    var n = Object.getOwnPropertySymbols(e);
    t && (n = n.filter(function(a) {
      return Object.getOwnPropertyDescriptor(e, a).enumerable;
    })), r.push.apply(r, n);
  }
  return r;
}
function gx(e) {
  for (var t = 1; t < arguments.length; t++) {
    var r = arguments[t] != null ? arguments[t] : {};
    t % 2 ? mx(Object(r), !0).forEach(function(n) {
      t5(e, n, r[n]);
    }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(r)) : mx(Object(r)).forEach(function(n) {
      Object.defineProperty(e, n, Object.getOwnPropertyDescriptor(r, n));
    });
  }
  return e;
}
function t5(e, t, r) {
  return t = r5(t), t in e ? Object.defineProperty(e, t, { value: r, enumerable: !0, configurable: !0, writable: !0 }) : e[t] = r, e;
}
function r5(e) {
  var t = n5(e, "string");
  return mi(t) == "symbol" ? t : t + "";
}
function n5(e, t) {
  if (mi(e) != "object" || !e) return e;
  var r = e[Symbol.toPrimitive];
  if (r !== void 0) {
    var n = r.call(e, t);
    if (mi(n) != "object") return n;
    throw new TypeError("@@toPrimitive must return a primitive value.");
  }
  return (t === "string" ? String : Number)(e);
}
var bx = function(t, r, n, a, i) {
  var o = n - a, u;
  return u = "M ".concat(t, ",").concat(r), u += "L ".concat(t + n, ",").concat(r), u += "L ".concat(t + n - o / 2, ",").concat(r + i), u += "L ".concat(t + n - o / 2 - a, ",").concat(r + i), u += "L ".concat(t, ",").concat(r, " Z"), u;
}, a5 = {
  x: 0,
  y: 0,
  upperWidth: 0,
  lowerWidth: 0,
  height: 0,
  isUpdateAnimationActive: !1,
  animationBegin: 0,
  animationDuration: 1500,
  animationEasing: "ease"
}, i5 = function(t) {
  var r = gx(gx({}, a5), t), n = pr(), a = Oe(-1), i = Yq(a, 2), o = i[0], u = i[1];
  It(function() {
    if (n.current && n.current.getTotalLength)
      try {
        var w = n.current.getTotalLength();
        w && u(w);
      } catch {
      }
  }, []);
  var l = r.x, s = r.y, f = r.upperWidth, c = r.lowerWidth, d = r.height, h = r.className, y = r.animationEasing, v = r.animationDuration, p = r.animationBegin, g = r.isUpdateAnimationActive;
  if (l !== +l || s !== +s || f !== +f || c !== +c || d !== +d || f === 0 && c === 0 || d === 0)
    return null;
  var b = _e("recharts-trapezoid", h);
  return g ? /* @__PURE__ */ M.createElement(gr, {
    canBegin: o > 0,
    from: {
      upperWidth: 0,
      lowerWidth: 0,
      height: d,
      x: l,
      y: s
    },
    to: {
      upperWidth: f,
      lowerWidth: c,
      height: d,
      x: l,
      y: s
    },
    duration: v,
    animationEasing: y,
    isActive: g
  }, function(w) {
    var _ = w.upperWidth, m = w.lowerWidth, O = w.height, x = w.x, S = w.y;
    return /* @__PURE__ */ M.createElement(gr, {
      canBegin: o > 0,
      from: "0px ".concat(o === -1 ? 1 : o, "px"),
      to: "".concat(o, "px 0px"),
      attributeName: "strokeDasharray",
      begin: p,
      duration: v,
      easing: y
    }, /* @__PURE__ */ M.createElement("path", Yo({}, pe(r, !0), {
      className: b,
      d: bx(x, S, _, m, O),
      ref: n
    })));
  }) : /* @__PURE__ */ M.createElement("g", null, /* @__PURE__ */ M.createElement("path", Yo({}, pe(r, !0), {
    className: b,
    d: bx(l, s, f, c, d)
  })));
}, o5 = ["option", "shapeType", "propTransformer", "activeClassName", "isActive"];
function gi(e) {
  "@babel/helpers - typeof";
  return gi = typeof Symbol == "function" && typeof Symbol.iterator == "symbol" ? function(t) {
    return typeof t;
  } : function(t) {
    return t && typeof Symbol == "function" && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t;
  }, gi(e);
}
function u5(e, t) {
  if (e == null) return {};
  var r = l5(e, t), n, a;
  if (Object.getOwnPropertySymbols) {
    var i = Object.getOwnPropertySymbols(e);
    for (a = 0; a < i.length; a++)
      n = i[a], !(t.indexOf(n) >= 0) && Object.prototype.propertyIsEnumerable.call(e, n) && (r[n] = e[n]);
  }
  return r;
}
function l5(e, t) {
  if (e == null) return {};
  var r = {};
  for (var n in e)
    if (Object.prototype.hasOwnProperty.call(e, n)) {
      if (t.indexOf(n) >= 0) continue;
      r[n] = e[n];
    }
  return r;
}
function xx(e, t) {
  var r = Object.keys(e);
  if (Object.getOwnPropertySymbols) {
    var n = Object.getOwnPropertySymbols(e);
    t && (n = n.filter(function(a) {
      return Object.getOwnPropertyDescriptor(e, a).enumerable;
    })), r.push.apply(r, n);
  }
  return r;
}
function Zo(e) {
  for (var t = 1; t < arguments.length; t++) {
    var r = arguments[t] != null ? arguments[t] : {};
    t % 2 ? xx(Object(r), !0).forEach(function(n) {
      s5(e, n, r[n]);
    }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(r)) : xx(Object(r)).forEach(function(n) {
      Object.defineProperty(e, n, Object.getOwnPropertyDescriptor(r, n));
    });
  }
  return e;
}
function s5(e, t, r) {
  return t = c5(t), t in e ? Object.defineProperty(e, t, { value: r, enumerable: !0, configurable: !0, writable: !0 }) : e[t] = r, e;
}
function c5(e) {
  var t = f5(e, "string");
  return gi(t) == "symbol" ? t : t + "";
}
function f5(e, t) {
  if (gi(e) != "object" || !e) return e;
  var r = e[Symbol.toPrimitive];
  if (r !== void 0) {
    var n = r.call(e, t);
    if (gi(n) != "object") return n;
    throw new TypeError("@@toPrimitive must return a primitive value.");
  }
  return (t === "string" ? String : Number)(e);
}
function d5(e, t) {
  return Zo(Zo({}, t), e);
}
function h5(e, t) {
  return e === "symbols";
}
function wx(e) {
  var t = e.shapeType, r = e.elementProps;
  switch (t) {
    case "rectangle":
      return /* @__PURE__ */ M.createElement(kp, r);
    case "trapezoid":
      return /* @__PURE__ */ M.createElement(i5, r);
    case "sector":
      return /* @__PURE__ */ M.createElement(m_, r);
    case "symbols":
      if (h5(t))
        return /* @__PURE__ */ M.createElement(ep, r);
      break;
    default:
      return null;
  }
}
function p5(e) {
  return /* @__PURE__ */ Lt(e) ? e.props : e;
}
function v5(e) {
  var t = e.option, r = e.shapeType, n = e.propTransformer, a = n === void 0 ? d5 : n, i = e.activeClassName, o = i === void 0 ? "recharts-active-shape" : i, u = e.isActive, l = u5(e, o5), s;
  if (/* @__PURE__ */ Lt(t))
    s = /* @__PURE__ */ Ue(t, Zo(Zo({}, l), p5(t)));
  else if (fe(t))
    s = t(l);
  else if (Gq(t) && !Xq(t)) {
    var f = a(t, l);
    s = /* @__PURE__ */ M.createElement(wx, {
      shapeType: r,
      elementProps: f
    });
  } else {
    var c = l;
    s = /* @__PURE__ */ M.createElement(wx, {
      shapeType: r,
      elementProps: c
    });
  }
  return u ? /* @__PURE__ */ M.createElement(Ie, {
    className: o
  }, s) : s;
}
function qu(e, t) {
  return t != null && "trapezoids" in e.props;
}
function Bu(e, t) {
  return t != null && "sectors" in e.props;
}
function bi(e, t) {
  return t != null && "points" in e.props;
}
function y5(e, t) {
  var r, n, a = e.x === (t == null || (r = t.labelViewBox) === null || r === void 0 ? void 0 : r.x) || e.x === t.x, i = e.y === (t == null || (n = t.labelViewBox) === null || n === void 0 ? void 0 : n.y) || e.y === t.y;
  return a && i;
}
function m5(e, t) {
  var r = e.endAngle === t.endAngle, n = e.startAngle === t.startAngle;
  return r && n;
}
function g5(e, t) {
  var r = e.x === t.x, n = e.y === t.y, a = e.z === t.z;
  return r && n && a;
}
function b5(e, t) {
  var r;
  return qu(e, t) ? r = y5 : Bu(e, t) ? r = m5 : bi(e, t) && (r = g5), r;
}
function x5(e, t) {
  var r;
  return qu(e, t) ? r = "trapezoids" : Bu(e, t) ? r = "sectors" : bi(e, t) && (r = "points"), r;
}
function w5(e, t) {
  if (qu(e, t)) {
    var r;
    return (r = t.tooltipPayload) === null || r === void 0 || (r = r[0]) === null || r === void 0 || (r = r.payload) === null || r === void 0 ? void 0 : r.payload;
  }
  if (Bu(e, t)) {
    var n;
    return (n = t.tooltipPayload) === null || n === void 0 || (n = n[0]) === null || n === void 0 || (n = n.payload) === null || n === void 0 ? void 0 : n.payload;
  }
  return bi(e, t) ? t.payload : {};
}
function O5(e) {
  var t = e.activeTooltipItem, r = e.graphicalItem, n = e.itemData, a = x5(r, t), i = w5(r, t), o = n.filter(function(l, s) {
    var f = ri(i, l), c = r.props[a].filter(function(y) {
      var v = b5(r, t);
      return v(y, t);
    }), d = r.props[a].indexOf(c[c.length - 1]), h = s === d;
    return f && h;
  }), u = n.indexOf(o[o.length - 1]);
  return u;
}
var Df, Ox;
function _5() {
  if (Ox) return Df;
  Ox = 1;
  var e = Math.ceil, t = Math.max;
  function r(n, a, i, o) {
    for (var u = -1, l = t(e((a - n) / (i || 1)), 0), s = Array(l); l--; )
      s[o ? l : ++u] = n, n += i;
    return s;
  }
  return Df = r, Df;
}
var Lf, _x;
function P_() {
  if (_x) return Lf;
  _x = 1;
  var e = oO(), t = 1 / 0, r = 17976931348623157e292;
  function n(a) {
    if (!a)
      return a === 0 ? a : 0;
    if (a = e(a), a === t || a === -t) {
      var i = a < 0 ? -1 : 1;
      return i * r;
    }
    return a === a ? a : 0;
  }
  return Lf = n, Lf;
}
var qf, Sx;
function S5() {
  if (Sx) return qf;
  Sx = 1;
  var e = _5(), t = Tu(), r = P_();
  function n(a) {
    return function(i, o, u) {
      return u && typeof u != "number" && t(i, o, u) && (o = u = void 0), i = r(i), o === void 0 ? (o = i, i = 0) : o = r(o), u = u === void 0 ? i < o ? 1 : -1 : r(u), e(i, o, u, a);
    };
  }
  return qf = n, qf;
}
var Bf, Px;
function P5() {
  if (Px) return Bf;
  Px = 1;
  var e = S5(), t = e();
  return Bf = t, Bf;
}
var A5 = P5();
const Jo = /* @__PURE__ */ $e(A5);
function xi(e) {
  "@babel/helpers - typeof";
  return xi = typeof Symbol == "function" && typeof Symbol.iterator == "symbol" ? function(t) {
    return typeof t;
  } : function(t) {
    return t && typeof Symbol == "function" && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t;
  }, xi(e);
}
function Ax(e, t) {
  var r = Object.keys(e);
  if (Object.getOwnPropertySymbols) {
    var n = Object.getOwnPropertySymbols(e);
    t && (n = n.filter(function(a) {
      return Object.getOwnPropertyDescriptor(e, a).enumerable;
    })), r.push.apply(r, n);
  }
  return r;
}
function Ex(e) {
  for (var t = 1; t < arguments.length; t++) {
    var r = arguments[t] != null ? arguments[t] : {};
    t % 2 ? Ax(Object(r), !0).forEach(function(n) {
      A_(e, n, r[n]);
    }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(r)) : Ax(Object(r)).forEach(function(n) {
      Object.defineProperty(e, n, Object.getOwnPropertyDescriptor(r, n));
    });
  }
  return e;
}
function A_(e, t, r) {
  return t = E5(t), t in e ? Object.defineProperty(e, t, { value: r, enumerable: !0, configurable: !0, writable: !0 }) : e[t] = r, e;
}
function E5(e) {
  var t = T5(e, "string");
  return xi(t) == "symbol" ? t : t + "";
}
function T5(e, t) {
  if (xi(e) != "object" || !e) return e;
  var r = e[Symbol.toPrimitive];
  if (r !== void 0) {
    var n = r.call(e, t);
    if (xi(n) != "object") return n;
    throw new TypeError("@@toPrimitive must return a primitive value.");
  }
  return (t === "string" ? String : Number)(e);
}
var M5 = ["Webkit", "Moz", "O", "ms"], j5 = function(t, r) {
  var n = t.replace(/(\w)/, function(i) {
    return i.toUpperCase();
  }), a = M5.reduce(function(i, o) {
    return Ex(Ex({}, i), {}, A_({}, o + n, r));
  }, {});
  return a[t] = r, a;
};
function Hn(e) {
  "@babel/helpers - typeof";
  return Hn = typeof Symbol == "function" && typeof Symbol.iterator == "symbol" ? function(t) {
    return typeof t;
  } : function(t) {
    return t && typeof Symbol == "function" && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t;
  }, Hn(e);
}
function Qo() {
  return Qo = Object.assign ? Object.assign.bind() : function(e) {
    for (var t = 1; t < arguments.length; t++) {
      var r = arguments[t];
      for (var n in r)
        Object.prototype.hasOwnProperty.call(r, n) && (e[n] = r[n]);
    }
    return e;
  }, Qo.apply(this, arguments);
}
function Tx(e, t) {
  var r = Object.keys(e);
  if (Object.getOwnPropertySymbols) {
    var n = Object.getOwnPropertySymbols(e);
    t && (n = n.filter(function(a) {
      return Object.getOwnPropertyDescriptor(e, a).enumerable;
    })), r.push.apply(r, n);
  }
  return r;
}
function Ff(e) {
  for (var t = 1; t < arguments.length; t++) {
    var r = arguments[t] != null ? arguments[t] : {};
    t % 2 ? Tx(Object(r), !0).forEach(function(n) {
      pt(e, n, r[n]);
    }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(r)) : Tx(Object(r)).forEach(function(n) {
      Object.defineProperty(e, n, Object.getOwnPropertyDescriptor(r, n));
    });
  }
  return e;
}
function N5(e, t) {
  if (!(e instanceof t))
    throw new TypeError("Cannot call a class as a function");
}
function Mx(e, t) {
  for (var r = 0; r < t.length; r++) {
    var n = t[r];
    n.enumerable = n.enumerable || !1, n.configurable = !0, "value" in n && (n.writable = !0), Object.defineProperty(e, T_(n.key), n);
  }
}
function C5(e, t, r) {
  return t && Mx(e.prototype, t), r && Mx(e, r), Object.defineProperty(e, "prototype", { writable: !1 }), e;
}
function $5(e, t, r) {
  return t = eu(t), R5(e, E_() ? Reflect.construct(t, r || [], eu(e).constructor) : t.apply(e, r));
}
function R5(e, t) {
  if (t && (Hn(t) === "object" || typeof t == "function"))
    return t;
  if (t !== void 0)
    throw new TypeError("Derived constructors may only return object or undefined");
  return k5(e);
}
function k5(e) {
  if (e === void 0)
    throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
  return e;
}
function E_() {
  try {
    var e = !Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function() {
    }));
  } catch {
  }
  return (E_ = function() {
    return !!e;
  })();
}
function eu(e) {
  return eu = Object.setPrototypeOf ? Object.getPrototypeOf.bind() : function(r) {
    return r.__proto__ || Object.getPrototypeOf(r);
  }, eu(e);
}
function I5(e, t) {
  if (typeof t != "function" && t !== null)
    throw new TypeError("Super expression must either be null or a function");
  e.prototype = Object.create(t && t.prototype, { constructor: { value: e, writable: !0, configurable: !0 } }), Object.defineProperty(e, "prototype", { writable: !1 }), t && oh(e, t);
}
function oh(e, t) {
  return oh = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function(n, a) {
    return n.__proto__ = a, n;
  }, oh(e, t);
}
function pt(e, t, r) {
  return t = T_(t), t in e ? Object.defineProperty(e, t, { value: r, enumerable: !0, configurable: !0, writable: !0 }) : e[t] = r, e;
}
function T_(e) {
  var t = D5(e, "string");
  return Hn(t) == "symbol" ? t : t + "";
}
function D5(e, t) {
  if (Hn(e) != "object" || !e) return e;
  var r = e[Symbol.toPrimitive];
  if (r !== void 0) {
    var n = r.call(e, t);
    if (Hn(n) != "object") return n;
    throw new TypeError("@@toPrimitive must return a primitive value.");
  }
  return String(e);
}
var L5 = function(t) {
  var r = t.data, n = t.startIndex, a = t.endIndex, i = t.x, o = t.width, u = t.travellerWidth;
  if (!r || !r.length)
    return {};
  var l = r.length, s = Ia().domain(Jo(0, l)).range([i, i + o - u]), f = s.domain().map(function(c) {
    return s(c);
  });
  return {
    isTextActive: !1,
    isSlideMoving: !1,
    isTravellerMoving: !1,
    isTravellerFocused: !1,
    startX: s(n),
    endX: s(a),
    scale: s,
    scaleValues: f
  };
}, jx = function(t) {
  return t.changedTouches && !!t.changedTouches.length;
}, Gn = /* @__PURE__ */ (function(e) {
  function t(r) {
    var n;
    return N5(this, t), n = $5(this, t, [r]), pt(n, "handleDrag", function(a) {
      n.leaveTimer && (clearTimeout(n.leaveTimer), n.leaveTimer = null), n.state.isTravellerMoving ? n.handleTravellerMove(a) : n.state.isSlideMoving && n.handleSlideDrag(a);
    }), pt(n, "handleTouchMove", function(a) {
      a.changedTouches != null && a.changedTouches.length > 0 && n.handleDrag(a.changedTouches[0]);
    }), pt(n, "handleDragEnd", function() {
      n.setState({
        isTravellerMoving: !1,
        isSlideMoving: !1
      }, function() {
        var a = n.props, i = a.endIndex, o = a.onDragEnd, u = a.startIndex;
        o?.({
          endIndex: i,
          startIndex: u
        });
      }), n.detachDragEndListener();
    }), pt(n, "handleLeaveWrapper", function() {
      (n.state.isTravellerMoving || n.state.isSlideMoving) && (n.leaveTimer = window.setTimeout(n.handleDragEnd, n.props.leaveTimeOut));
    }), pt(n, "handleEnterSlideOrTraveller", function() {
      n.setState({
        isTextActive: !0
      });
    }), pt(n, "handleLeaveSlideOrTraveller", function() {
      n.setState({
        isTextActive: !1
      });
    }), pt(n, "handleSlideDragStart", function(a) {
      var i = jx(a) ? a.changedTouches[0] : a;
      n.setState({
        isTravellerMoving: !1,
        isSlideMoving: !0,
        slideMoveStartX: i.pageX
      }), n.attachDragEndListener();
    }), n.travellerDragStartHandlers = {
      startX: n.handleTravellerDragStart.bind(n, "startX"),
      endX: n.handleTravellerDragStart.bind(n, "endX")
    }, n.state = {}, n;
  }
  return I5(t, e), C5(t, [{
    key: "componentWillUnmount",
    value: function() {
      this.leaveTimer && (clearTimeout(this.leaveTimer), this.leaveTimer = null), this.detachDragEndListener();
    }
  }, {
    key: "getIndex",
    value: function(n) {
      var a = n.startX, i = n.endX, o = this.state.scaleValues, u = this.props, l = u.gap, s = u.data, f = s.length - 1, c = Math.min(a, i), d = Math.max(a, i), h = t.getIndexInRange(o, c), y = t.getIndexInRange(o, d);
      return {
        startIndex: h - h % l,
        endIndex: y === f ? f : y - y % l
      };
    }
  }, {
    key: "getTextOfTick",
    value: function(n) {
      var a = this.props, i = a.data, o = a.tickFormatter, u = a.dataKey, l = gt(i[n], u, n);
      return fe(o) ? o(l, n) : l;
    }
  }, {
    key: "attachDragEndListener",
    value: function() {
      window.addEventListener("mouseup", this.handleDragEnd, !0), window.addEventListener("touchend", this.handleDragEnd, !0), window.addEventListener("mousemove", this.handleDrag, !0);
    }
  }, {
    key: "detachDragEndListener",
    value: function() {
      window.removeEventListener("mouseup", this.handleDragEnd, !0), window.removeEventListener("touchend", this.handleDragEnd, !0), window.removeEventListener("mousemove", this.handleDrag, !0);
    }
  }, {
    key: "handleSlideDrag",
    value: function(n) {
      var a = this.state, i = a.slideMoveStartX, o = a.startX, u = a.endX, l = this.props, s = l.x, f = l.width, c = l.travellerWidth, d = l.startIndex, h = l.endIndex, y = l.onChange, v = n.pageX - i;
      v > 0 ? v = Math.min(v, s + f - c - u, s + f - c - o) : v < 0 && (v = Math.max(v, s - o, s - u));
      var p = this.getIndex({
        startX: o + v,
        endX: u + v
      });
      (p.startIndex !== d || p.endIndex !== h) && y && y(p), this.setState({
        startX: o + v,
        endX: u + v,
        slideMoveStartX: n.pageX
      });
    }
  }, {
    key: "handleTravellerDragStart",
    value: function(n, a) {
      var i = jx(a) ? a.changedTouches[0] : a;
      this.setState({
        isSlideMoving: !1,
        isTravellerMoving: !0,
        movingTravellerId: n,
        brushMoveStartX: i.pageX
      }), this.attachDragEndListener();
    }
  }, {
    key: "handleTravellerMove",
    value: function(n) {
      var a = this.state, i = a.brushMoveStartX, o = a.movingTravellerId, u = a.endX, l = a.startX, s = this.state[o], f = this.props, c = f.x, d = f.width, h = f.travellerWidth, y = f.onChange, v = f.gap, p = f.data, g = {
        startX: this.state.startX,
        endX: this.state.endX
      }, b = n.pageX - i;
      b > 0 ? b = Math.min(b, c + d - h - s) : b < 0 && (b = Math.max(b, c - s)), g[o] = s + b;
      var w = this.getIndex(g), _ = w.startIndex, m = w.endIndex, O = function() {
        var S = p.length - 1;
        return o === "startX" && (u > l ? _ % v === 0 : m % v === 0) || u < l && m === S || o === "endX" && (u > l ? m % v === 0 : _ % v === 0) || u > l && m === S;
      };
      this.setState(pt(pt({}, o, s + b), "brushMoveStartX", n.pageX), function() {
        y && O() && y(w);
      });
    }
  }, {
    key: "handleTravellerMoveKeyboard",
    value: function(n, a) {
      var i = this, o = this.state, u = o.scaleValues, l = o.startX, s = o.endX, f = this.state[a], c = u.indexOf(f);
      if (c !== -1) {
        var d = c + n;
        if (!(d === -1 || d >= u.length)) {
          var h = u[d];
          a === "startX" && h >= s || a === "endX" && h <= l || this.setState(pt({}, a, h), function() {
            i.props.onChange(i.getIndex({
              startX: i.state.startX,
              endX: i.state.endX
            }));
          });
        }
      }
    }
  }, {
    key: "renderBackground",
    value: function() {
      var n = this.props, a = n.x, i = n.y, o = n.width, u = n.height, l = n.fill, s = n.stroke;
      return /* @__PURE__ */ M.createElement("rect", {
        stroke: s,
        fill: l,
        x: a,
        y: i,
        width: o,
        height: u
      });
    }
  }, {
    key: "renderPanorama",
    value: function() {
      var n = this.props, a = n.x, i = n.y, o = n.width, u = n.height, l = n.data, s = n.children, f = n.padding, c = $r.only(s);
      return c ? /* @__PURE__ */ M.cloneElement(c, {
        x: a,
        y: i,
        width: o,
        height: u,
        margin: f,
        compact: !0,
        data: l
      }) : null;
    }
  }, {
    key: "renderTravellerLayer",
    value: function(n, a) {
      var i, o, u = this, l = this.props, s = l.y, f = l.travellerWidth, c = l.height, d = l.traveller, h = l.ariaLabel, y = l.data, v = l.startIndex, p = l.endIndex, g = Math.max(n, this.props.x), b = Ff(Ff({}, pe(this.props, !1)), {}, {
        x: g,
        y: s,
        width: f,
        height: c
      }), w = h || "Min value: ".concat((i = y[v]) === null || i === void 0 ? void 0 : i.name, ", Max value: ").concat((o = y[p]) === null || o === void 0 ? void 0 : o.name);
      return /* @__PURE__ */ M.createElement(Ie, {
        tabIndex: 0,
        role: "slider",
        "aria-label": w,
        "aria-valuenow": n,
        className: "recharts-brush-traveller",
        onMouseEnter: this.handleEnterSlideOrTraveller,
        onMouseLeave: this.handleLeaveSlideOrTraveller,
        onMouseDown: this.travellerDragStartHandlers[a],
        onTouchStart: this.travellerDragStartHandlers[a],
        onKeyDown: function(m) {
          ["ArrowLeft", "ArrowRight"].includes(m.key) && (m.preventDefault(), m.stopPropagation(), u.handleTravellerMoveKeyboard(m.key === "ArrowRight" ? 1 : -1, a));
        },
        onFocus: function() {
          u.setState({
            isTravellerFocused: !0
          });
        },
        onBlur: function() {
          u.setState({
            isTravellerFocused: !1
          });
        },
        style: {
          cursor: "col-resize"
        }
      }, t.renderTraveller(d, b));
    }
  }, {
    key: "renderSlide",
    value: function(n, a) {
      var i = this.props, o = i.y, u = i.height, l = i.stroke, s = i.travellerWidth, f = Math.min(n, a) + s, c = Math.max(Math.abs(a - n) - s, 0);
      return /* @__PURE__ */ M.createElement("rect", {
        className: "recharts-brush-slide",
        onMouseEnter: this.handleEnterSlideOrTraveller,
        onMouseLeave: this.handleLeaveSlideOrTraveller,
        onMouseDown: this.handleSlideDragStart,
        onTouchStart: this.handleSlideDragStart,
        style: {
          cursor: "move"
        },
        stroke: "none",
        fill: l,
        fillOpacity: 0.2,
        x: f,
        y: o,
        width: c,
        height: u
      });
    }
  }, {
    key: "renderText",
    value: function() {
      var n = this.props, a = n.startIndex, i = n.endIndex, o = n.y, u = n.height, l = n.travellerWidth, s = n.stroke, f = this.state, c = f.startX, d = f.endX, h = 5, y = {
        pointerEvents: "none",
        fill: s
      };
      return /* @__PURE__ */ M.createElement(Ie, {
        className: "recharts-brush-texts"
      }, /* @__PURE__ */ M.createElement(Mo, Qo({
        textAnchor: "end",
        verticalAnchor: "middle",
        x: Math.min(c, d) - h,
        y: o + u / 2
      }, y), this.getTextOfTick(a)), /* @__PURE__ */ M.createElement(Mo, Qo({
        textAnchor: "start",
        verticalAnchor: "middle",
        x: Math.max(c, d) + l + h,
        y: o + u / 2
      }, y), this.getTextOfTick(i)));
    }
  }, {
    key: "render",
    value: function() {
      var n = this.props, a = n.data, i = n.className, o = n.children, u = n.x, l = n.y, s = n.width, f = n.height, c = n.alwaysShowText, d = this.state, h = d.startX, y = d.endX, v = d.isTextActive, p = d.isSlideMoving, g = d.isTravellerMoving, b = d.isTravellerFocused;
      if (!a || !a.length || !H(u) || !H(l) || !H(s) || !H(f) || s <= 0 || f <= 0)
        return null;
      var w = _e("recharts-brush", i), _ = M.Children.count(o) === 1, m = j5("userSelect", "none");
      return /* @__PURE__ */ M.createElement(Ie, {
        className: w,
        onMouseLeave: this.handleLeaveWrapper,
        onTouchMove: this.handleTouchMove,
        style: m
      }, this.renderBackground(), _ && this.renderPanorama(), this.renderSlide(h, y), this.renderTravellerLayer(h, "startX"), this.renderTravellerLayer(y, "endX"), (v || p || g || b || c) && this.renderText());
    }
  }], [{
    key: "renderDefaultTraveller",
    value: function(n) {
      var a = n.x, i = n.y, o = n.width, u = n.height, l = n.stroke, s = Math.floor(i + u / 2) - 1;
      return /* @__PURE__ */ M.createElement(M.Fragment, null, /* @__PURE__ */ M.createElement("rect", {
        x: a,
        y: i,
        width: o,
        height: u,
        fill: l,
        stroke: "none"
      }), /* @__PURE__ */ M.createElement("line", {
        x1: a + 1,
        y1: s,
        x2: a + o - 1,
        y2: s,
        fill: "none",
        stroke: "#fff"
      }), /* @__PURE__ */ M.createElement("line", {
        x1: a + 1,
        y1: s + 2,
        x2: a + o - 1,
        y2: s + 2,
        fill: "none",
        stroke: "#fff"
      }));
    }
  }, {
    key: "renderTraveller",
    value: function(n, a) {
      var i;
      return /* @__PURE__ */ M.isValidElement(n) ? i = /* @__PURE__ */ M.cloneElement(n, a) : fe(n) ? i = n(a) : i = t.renderDefaultTraveller(a), i;
    }
  }, {
    key: "getDerivedStateFromProps",
    value: function(n, a) {
      var i = n.data, o = n.width, u = n.x, l = n.travellerWidth, s = n.updateId, f = n.startIndex, c = n.endIndex;
      if (i !== a.prevData || s !== a.prevUpdateId)
        return Ff({
          prevData: i,
          prevTravellerWidth: l,
          prevUpdateId: s,
          prevX: u,
          prevWidth: o
        }, i && i.length ? L5({
          data: i,
          width: o,
          x: u,
          travellerWidth: l,
          startIndex: f,
          endIndex: c
        }) : {
          scale: null,
          scaleValues: null
        });
      if (a.scale && (o !== a.prevWidth || u !== a.prevX || l !== a.prevTravellerWidth)) {
        a.scale.range([u, u + o - l]);
        var d = a.scale.domain().map(function(h) {
          return a.scale(h);
        });
        return {
          prevData: i,
          prevTravellerWidth: l,
          prevUpdateId: s,
          prevX: u,
          prevWidth: o,
          startX: a.scale(n.startIndex),
          endX: a.scale(n.endIndex),
          scaleValues: d
        };
      }
      return null;
    }
  }, {
    key: "getIndexInRange",
    value: function(n, a) {
      for (var i = n.length, o = 0, u = i - 1; u - o > 1; ) {
        var l = Math.floor((o + u) / 2);
        n[l] > a ? u = l : o = l;
      }
      return a >= n[u] ? u : o;
    }
  }]);
})(br);
pt(Gn, "displayName", "Brush");
pt(Gn, "defaultProps", {
  height: 40,
  travellerWidth: 5,
  gap: 1,
  fill: "#fff",
  stroke: "#666",
  padding: {
    top: 1,
    right: 1,
    bottom: 1,
    left: 1
  },
  leaveTimeOut: 1e3,
  alwaysShowText: !1
});
var zf, Nx;
function q5() {
  if (Nx) return zf;
  Nx = 1;
  var e = up();
  function t(r, n) {
    var a;
    return e(r, function(i, o, u) {
      return a = n(i, o, u), !a;
    }), !!a;
  }
  return zf = t, zf;
}
var Uf, Cx;
function B5() {
  if (Cx) return Uf;
  Cx = 1;
  var e = Dw(), t = qr(), r = q5(), n = ht(), a = Tu();
  function i(o, u, l) {
    var s = n(o) ? e : r;
    return l && a(o, u, l) && (u = void 0), s(o, t(u, 3));
  }
  return Uf = i, Uf;
}
var F5 = B5();
const z5 = /* @__PURE__ */ $e(F5);
var Xt = function(t, r) {
  var n = t.alwaysShow, a = t.ifOverflow;
  return n && (a = "extendDomain"), a === r;
}, Wf, $x;
function U5() {
  if ($x) return Wf;
  $x = 1;
  var e = tO();
  function t(r, n, a) {
    n == "__proto__" && e ? e(r, n, {
      configurable: !0,
      enumerable: !0,
      value: a,
      writable: !0
    }) : r[n] = a;
  }
  return Wf = t, Wf;
}
var Hf, Rx;
function W5() {
  if (Rx) return Hf;
  Rx = 1;
  var e = U5(), t = Qw(), r = qr();
  function n(a, i) {
    var o = {};
    return i = r(i, 3), t(a, function(u, l, s) {
      e(o, l, i(u, l, s));
    }), o;
  }
  return Hf = n, Hf;
}
var H5 = W5();
const G5 = /* @__PURE__ */ $e(H5);
var Gf, kx;
function K5() {
  if (kx) return Gf;
  kx = 1;
  function e(t, r) {
    for (var n = -1, a = t == null ? 0 : t.length; ++n < a; )
      if (!r(t[n], n, t))
        return !1;
    return !0;
  }
  return Gf = e, Gf;
}
var Kf, Ix;
function V5() {
  if (Ix) return Kf;
  Ix = 1;
  var e = up();
  function t(r, n) {
    var a = !0;
    return e(r, function(i, o, u) {
      return a = !!n(i, o, u), a;
    }), a;
  }
  return Kf = t, Kf;
}
var Vf, Dx;
function X5() {
  if (Dx) return Vf;
  Dx = 1;
  var e = K5(), t = V5(), r = qr(), n = ht(), a = Tu();
  function i(o, u, l) {
    var s = n(o) ? e : t;
    return l && a(o, u, l) && (u = void 0), s(o, r(u, 3));
  }
  return Vf = i, Vf;
}
var Y5 = X5();
const M_ = /* @__PURE__ */ $e(Y5);
var Z5 = ["x", "y"];
function wi(e) {
  "@babel/helpers - typeof";
  return wi = typeof Symbol == "function" && typeof Symbol.iterator == "symbol" ? function(t) {
    return typeof t;
  } : function(t) {
    return t && typeof Symbol == "function" && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t;
  }, wi(e);
}
function uh() {
  return uh = Object.assign ? Object.assign.bind() : function(e) {
    for (var t = 1; t < arguments.length; t++) {
      var r = arguments[t];
      for (var n in r)
        Object.prototype.hasOwnProperty.call(r, n) && (e[n] = r[n]);
    }
    return e;
  }, uh.apply(this, arguments);
}
function Lx(e, t) {
  var r = Object.keys(e);
  if (Object.getOwnPropertySymbols) {
    var n = Object.getOwnPropertySymbols(e);
    t && (n = n.filter(function(a) {
      return Object.getOwnPropertyDescriptor(e, a).enumerable;
    })), r.push.apply(r, n);
  }
  return r;
}
function Ea(e) {
  for (var t = 1; t < arguments.length; t++) {
    var r = arguments[t] != null ? arguments[t] : {};
    t % 2 ? Lx(Object(r), !0).forEach(function(n) {
      J5(e, n, r[n]);
    }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(r)) : Lx(Object(r)).forEach(function(n) {
      Object.defineProperty(e, n, Object.getOwnPropertyDescriptor(r, n));
    });
  }
  return e;
}
function J5(e, t, r) {
  return t = Q5(t), t in e ? Object.defineProperty(e, t, { value: r, enumerable: !0, configurable: !0, writable: !0 }) : e[t] = r, e;
}
function Q5(e) {
  var t = eB(e, "string");
  return wi(t) == "symbol" ? t : t + "";
}
function eB(e, t) {
  if (wi(e) != "object" || !e) return e;
  var r = e[Symbol.toPrimitive];
  if (r !== void 0) {
    var n = r.call(e, t);
    if (wi(n) != "object") return n;
    throw new TypeError("@@toPrimitive must return a primitive value.");
  }
  return (t === "string" ? String : Number)(e);
}
function tB(e, t) {
  if (e == null) return {};
  var r = rB(e, t), n, a;
  if (Object.getOwnPropertySymbols) {
    var i = Object.getOwnPropertySymbols(e);
    for (a = 0; a < i.length; a++)
      n = i[a], !(t.indexOf(n) >= 0) && Object.prototype.propertyIsEnumerable.call(e, n) && (r[n] = e[n]);
  }
  return r;
}
function rB(e, t) {
  if (e == null) return {};
  var r = {};
  for (var n in e)
    if (Object.prototype.hasOwnProperty.call(e, n)) {
      if (t.indexOf(n) >= 0) continue;
      r[n] = e[n];
    }
  return r;
}
function nB(e, t) {
  var r = e.x, n = e.y, a = tB(e, Z5), i = "".concat(r), o = parseInt(i, 10), u = "".concat(n), l = parseInt(u, 10), s = "".concat(t.height || a.height), f = parseInt(s, 10), c = "".concat(t.width || a.width), d = parseInt(c, 10);
  return Ea(Ea(Ea(Ea(Ea({}, t), a), o ? {
    x: o
  } : {}), l ? {
    y: l
  } : {}), {}, {
    height: f,
    width: d,
    name: t.name,
    radius: t.radius
  });
}
function qx(e) {
  return /* @__PURE__ */ M.createElement(v5, uh({
    shapeType: "rectangle",
    propTransformer: nB,
    activeClassName: "recharts-active-bar"
  }, e));
}
var aB = function(t) {
  var r = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : 0;
  return function(n, a) {
    if (typeof t == "number") return t;
    var i = typeof n == "number";
    return i ? t(n, a) : (i || ln(), r);
  };
}, iB = ["value", "background"], j_;
function Kn(e) {
  "@babel/helpers - typeof";
  return Kn = typeof Symbol == "function" && typeof Symbol.iterator == "symbol" ? function(t) {
    return typeof t;
  } : function(t) {
    return t && typeof Symbol == "function" && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t;
  }, Kn(e);
}
function oB(e, t) {
  if (e == null) return {};
  var r = uB(e, t), n, a;
  if (Object.getOwnPropertySymbols) {
    var i = Object.getOwnPropertySymbols(e);
    for (a = 0; a < i.length; a++)
      n = i[a], !(t.indexOf(n) >= 0) && Object.prototype.propertyIsEnumerable.call(e, n) && (r[n] = e[n]);
  }
  return r;
}
function uB(e, t) {
  if (e == null) return {};
  var r = {};
  for (var n in e)
    if (Object.prototype.hasOwnProperty.call(e, n)) {
      if (t.indexOf(n) >= 0) continue;
      r[n] = e[n];
    }
  return r;
}
function tu() {
  return tu = Object.assign ? Object.assign.bind() : function(e) {
    for (var t = 1; t < arguments.length; t++) {
      var r = arguments[t];
      for (var n in r)
        Object.prototype.hasOwnProperty.call(r, n) && (e[n] = r[n]);
    }
    return e;
  }, tu.apply(this, arguments);
}
function Bx(e, t) {
  var r = Object.keys(e);
  if (Object.getOwnPropertySymbols) {
    var n = Object.getOwnPropertySymbols(e);
    t && (n = n.filter(function(a) {
      return Object.getOwnPropertyDescriptor(e, a).enumerable;
    })), r.push.apply(r, n);
  }
  return r;
}
function ze(e) {
  for (var t = 1; t < arguments.length; t++) {
    var r = arguments[t] != null ? arguments[t] : {};
    t % 2 ? Bx(Object(r), !0).forEach(function(n) {
      Cr(e, n, r[n]);
    }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(r)) : Bx(Object(r)).forEach(function(n) {
      Object.defineProperty(e, n, Object.getOwnPropertyDescriptor(r, n));
    });
  }
  return e;
}
function lB(e, t) {
  if (!(e instanceof t))
    throw new TypeError("Cannot call a class as a function");
}
function Fx(e, t) {
  for (var r = 0; r < t.length; r++) {
    var n = t[r];
    n.enumerable = n.enumerable || !1, n.configurable = !0, "value" in n && (n.writable = !0), Object.defineProperty(e, C_(n.key), n);
  }
}
function sB(e, t, r) {
  return t && Fx(e.prototype, t), r && Fx(e, r), Object.defineProperty(e, "prototype", { writable: !1 }), e;
}
function cB(e, t, r) {
  return t = ru(t), fB(e, N_() ? Reflect.construct(t, r || [], ru(e).constructor) : t.apply(e, r));
}
function fB(e, t) {
  if (t && (Kn(t) === "object" || typeof t == "function"))
    return t;
  if (t !== void 0)
    throw new TypeError("Derived constructors may only return object or undefined");
  return dB(e);
}
function dB(e) {
  if (e === void 0)
    throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
  return e;
}
function N_() {
  try {
    var e = !Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function() {
    }));
  } catch {
  }
  return (N_ = function() {
    return !!e;
  })();
}
function ru(e) {
  return ru = Object.setPrototypeOf ? Object.getPrototypeOf.bind() : function(r) {
    return r.__proto__ || Object.getPrototypeOf(r);
  }, ru(e);
}
function hB(e, t) {
  if (typeof t != "function" && t !== null)
    throw new TypeError("Super expression must either be null or a function");
  e.prototype = Object.create(t && t.prototype, { constructor: { value: e, writable: !0, configurable: !0 } }), Object.defineProperty(e, "prototype", { writable: !1 }), t && lh(e, t);
}
function lh(e, t) {
  return lh = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function(n, a) {
    return n.__proto__ = a, n;
  }, lh(e, t);
}
function Cr(e, t, r) {
  return t = C_(t), t in e ? Object.defineProperty(e, t, { value: r, enumerable: !0, configurable: !0, writable: !0 }) : e[t] = r, e;
}
function C_(e) {
  var t = pB(e, "string");
  return Kn(t) == "symbol" ? t : t + "";
}
function pB(e, t) {
  if (Kn(e) != "object" || !e) return e;
  var r = e[Symbol.toPrimitive];
  if (r !== void 0) {
    var n = r.call(e, t);
    if (Kn(n) != "object") return n;
    throw new TypeError("@@toPrimitive must return a primitive value.");
  }
  return String(e);
}
var Bt = /* @__PURE__ */ (function(e) {
  function t() {
    var r;
    lB(this, t);
    for (var n = arguments.length, a = new Array(n), i = 0; i < n; i++)
      a[i] = arguments[i];
    return r = cB(this, t, [].concat(a)), Cr(r, "state", {
      isAnimationFinished: !1
    }), Cr(r, "id", $i("recharts-bar-")), Cr(r, "handleAnimationEnd", function() {
      var o = r.props.onAnimationEnd;
      r.setState({
        isAnimationFinished: !0
      }), o && o();
    }), Cr(r, "handleAnimationStart", function() {
      var o = r.props.onAnimationStart;
      r.setState({
        isAnimationFinished: !1
      }), o && o();
    }), r;
  }
  return hB(t, e), sB(t, [{
    key: "renderRectanglesStatically",
    value: function(n) {
      var a = this, i = this.props, o = i.shape, u = i.dataKey, l = i.activeIndex, s = i.activeBar, f = pe(this.props, !1);
      return n && n.map(function(c, d) {
        var h = d === l, y = h ? s : o, v = ze(ze(ze({}, f), c), {}, {
          isActive: h,
          option: y,
          index: d,
          dataKey: u,
          onAnimationStart: a.handleAnimationStart,
          onAnimationEnd: a.handleAnimationEnd
        });
        return /* @__PURE__ */ M.createElement(Ie, tu({
          className: "recharts-bar-rectangle"
        }, go(a.props, c, d), {
          // https://github.com/recharts/recharts/issues/5415
          // eslint-disable-next-line react/no-array-index-key
          key: "rectangle-".concat(c?.x, "-").concat(c?.y, "-").concat(c?.value, "-").concat(d)
        }), /* @__PURE__ */ M.createElement(qx, v));
      });
    }
  }, {
    key: "renderRectanglesWithAnimation",
    value: function() {
      var n = this, a = this.props, i = a.data, o = a.layout, u = a.isAnimationActive, l = a.animationBegin, s = a.animationDuration, f = a.animationEasing, c = a.animationId, d = this.state.prevData;
      return /* @__PURE__ */ M.createElement(gr, {
        begin: l,
        duration: s,
        isActive: u,
        easing: f,
        from: {
          t: 0
        },
        to: {
          t: 1
        },
        key: "bar-".concat(c),
        onAnimationEnd: this.handleAnimationEnd,
        onAnimationStart: this.handleAnimationStart
      }, function(h) {
        var y = h.t, v = i.map(function(p, g) {
          var b = d && d[g];
          if (b) {
            var w = At(b.x, p.x), _ = At(b.y, p.y), m = At(b.width, p.width), O = At(b.height, p.height);
            return ze(ze({}, p), {}, {
              x: w(y),
              y: _(y),
              width: m(y),
              height: O(y)
            });
          }
          if (o === "horizontal") {
            var x = At(0, p.height), S = x(y);
            return ze(ze({}, p), {}, {
              y: p.y + p.height - S,
              height: S
            });
          }
          var T = At(0, p.width), C = T(y);
          return ze(ze({}, p), {}, {
            width: C
          });
        });
        return /* @__PURE__ */ M.createElement(Ie, null, n.renderRectanglesStatically(v));
      });
    }
  }, {
    key: "renderRectangles",
    value: function() {
      var n = this.props, a = n.data, i = n.isAnimationActive, o = this.state.prevData;
      return i && a && a.length && (!o || !ri(o, a)) ? this.renderRectanglesWithAnimation() : this.renderRectanglesStatically(a);
    }
  }, {
    key: "renderBackground",
    value: function() {
      var n = this, a = this.props, i = a.data, o = a.dataKey, u = a.activeIndex, l = pe(this.props.background, !1);
      return i.map(function(s, f) {
        s.value;
        var c = s.background, d = oB(s, iB);
        if (!c)
          return null;
        var h = ze(ze(ze(ze(ze({}, d), {}, {
          fill: "#eee"
        }, c), l), go(n.props, s, f)), {}, {
          onAnimationStart: n.handleAnimationStart,
          onAnimationEnd: n.handleAnimationEnd,
          dataKey: o,
          index: f,
          className: "recharts-bar-background-rectangle"
        });
        return /* @__PURE__ */ M.createElement(qx, tu({
          key: "background-bar-".concat(f),
          option: n.props.background,
          isActive: f === u
        }, h));
      });
    }
  }, {
    key: "renderErrorBar",
    value: function(n, a) {
      if (this.props.isAnimationActive && !this.state.isAnimationFinished)
        return null;
      var i = this.props, o = i.data, u = i.xAxis, l = i.yAxis, s = i.layout, f = i.children, c = qt(f, Lu);
      if (!c)
        return null;
      var d = s === "vertical" ? o[0].height / 2 : o[0].width / 2, h = function(p, g) {
        var b = Array.isArray(p.value) ? p.value[1] : p.value;
        return {
          x: p.x,
          y: p.y,
          value: b,
          errorVal: gt(p, g)
        };
      }, y = {
        clipPath: n ? "url(#clipPath-".concat(a, ")") : null
      };
      return /* @__PURE__ */ M.createElement(Ie, y, c.map(function(v) {
        return /* @__PURE__ */ M.cloneElement(v, {
          key: "error-bar-".concat(a, "-").concat(v.props.dataKey),
          data: o,
          xAxis: u,
          yAxis: l,
          layout: s,
          offset: d,
          dataPointFormatter: h
        });
      }));
    }
  }, {
    key: "render",
    value: function() {
      var n = this.props, a = n.hide, i = n.data, o = n.className, u = n.xAxis, l = n.yAxis, s = n.left, f = n.top, c = n.width, d = n.height, h = n.isAnimationActive, y = n.background, v = n.id;
      if (a || !i || !i.length)
        return null;
      var p = this.state.isAnimationFinished, g = _e("recharts-bar", o), b = u && u.allowDataOverflow, w = l && l.allowDataOverflow, _ = b || w, m = me(v) ? this.id : v;
      return /* @__PURE__ */ M.createElement(Ie, {
        className: g
      }, b || w ? /* @__PURE__ */ M.createElement("defs", null, /* @__PURE__ */ M.createElement("clipPath", {
        id: "clipPath-".concat(m)
      }, /* @__PURE__ */ M.createElement("rect", {
        x: b ? s : s - c / 2,
        y: w ? f : f - d / 2,
        width: b ? c : c * 2,
        height: w ? d : d * 2
      }))) : null, /* @__PURE__ */ M.createElement(Ie, {
        className: "recharts-bar-rectangles",
        clipPath: _ ? "url(#clipPath-".concat(m, ")") : null
      }, y ? this.renderBackground() : null, this.renderRectangles()), this.renderErrorBar(_, m), (!h || p) && kr.renderCallByParent(this.props, i));
    }
  }], [{
    key: "getDerivedStateFromProps",
    value: function(n, a) {
      return n.animationId !== a.prevAnimationId ? {
        prevAnimationId: n.animationId,
        curData: n.data,
        prevData: a.curData
      } : n.data !== a.curData ? {
        curData: n.data
      } : null;
    }
  }]);
})(br);
j_ = Bt;
Cr(Bt, "displayName", "Bar");
Cr(Bt, "defaultProps", {
  xAxisId: 0,
  yAxisId: 0,
  legendType: "rect",
  minPointSize: 0,
  hide: !1,
  data: [],
  layout: "vertical",
  activeBar: !1,
  isAnimationActive: !ua.isSsr,
  animationBegin: 0,
  animationDuration: 400,
  animationEasing: "ease"
});
Cr(Bt, "getComposedData", function(e) {
  var t = e.props, r = e.item, n = e.barPosition, a = e.bandSize, i = e.xAxis, o = e.yAxis, u = e.xAxisTicks, l = e.yAxisTicks, s = e.stackedData, f = e.dataStartIndex, c = e.displayedData, d = e.offset, h = CI(n, r);
  if (!h)
    return null;
  var y = t.layout, v = r.type.defaultProps, p = v !== void 0 ? ze(ze({}, v), r.props) : r.props, g = p.dataKey, b = p.children, w = p.minPointSize, _ = y === "horizontal" ? o : i, m = s ? _.scale.domain() : null, O = BI({
    numericAxis: _
  }), x = qt(b, lO), S = c.map(function(T, C) {
    var A, N, $, D, R, L;
    s ? A = $I(s[f + C], m) : (A = gt(T, g), Array.isArray(A) || (A = [O, A]));
    var z = aB(w, j_.defaultProps.minPointSize)(A[1], C);
    if (y === "horizontal") {
      var F, W = [o.scale(A[0]), o.scale(A[1])], X = W[0], J = W[1];
      N = Sb({
        axis: i,
        ticks: u,
        bandSize: a,
        offset: h.offset,
        entry: T,
        index: C
      }), $ = (F = J ?? X) !== null && F !== void 0 ? F : void 0, D = h.size;
      var G = X - J;
      if (R = Number.isNaN(G) ? 0 : G, L = {
        x: N,
        y: o.y,
        width: D,
        height: o.height
      }, Math.abs(z) > 0 && Math.abs(R) < Math.abs(z)) {
        var Q = Dt(R || z) * (Math.abs(z) - Math.abs(R));
        $ -= Q, R += Q;
      }
    } else {
      var de = [i.scale(A[0]), i.scale(A[1])], ge = de[0], qe = de[1];
      if (N = ge, $ = Sb({
        axis: o,
        ticks: l,
        bandSize: a,
        offset: h.offset,
        entry: T,
        index: C
      }), D = qe - ge, R = h.size, L = {
        x: i.x,
        y: $,
        width: i.width,
        height: R
      }, Math.abs(z) > 0 && Math.abs(D) < Math.abs(z)) {
        var bt = Dt(D || z) * (Math.abs(z) - Math.abs(D));
        D += bt;
      }
    }
    return ze(ze(ze({}, T), {}, {
      x: N,
      y: $,
      width: D,
      height: R,
      value: s ? A : A[1],
      payload: T,
      background: L
    }, x && x[C] && x[C].props), {}, {
      tooltipPayload: [p_(r, T)],
      tooltipPosition: {
        x: N + D / 2,
        y: $ + R / 2
      }
    });
  });
  return ze({
    data: S,
    layout: y
  }, d);
});
function Oi(e) {
  "@babel/helpers - typeof";
  return Oi = typeof Symbol == "function" && typeof Symbol.iterator == "symbol" ? function(t) {
    return typeof t;
  } : function(t) {
    return t && typeof Symbol == "function" && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t;
  }, Oi(e);
}
function vB(e, t) {
  if (!(e instanceof t))
    throw new TypeError("Cannot call a class as a function");
}
function zx(e, t) {
  for (var r = 0; r < t.length; r++) {
    var n = t[r];
    n.enumerable = n.enumerable || !1, n.configurable = !0, "value" in n && (n.writable = !0), Object.defineProperty(e, $_(n.key), n);
  }
}
function yB(e, t, r) {
  return t && zx(e.prototype, t), r && zx(e, r), Object.defineProperty(e, "prototype", { writable: !1 }), e;
}
function Ux(e, t) {
  var r = Object.keys(e);
  if (Object.getOwnPropertySymbols) {
    var n = Object.getOwnPropertySymbols(e);
    t && (n = n.filter(function(a) {
      return Object.getOwnPropertyDescriptor(e, a).enumerable;
    })), r.push.apply(r, n);
  }
  return r;
}
function Rt(e) {
  for (var t = 1; t < arguments.length; t++) {
    var r = arguments[t] != null ? arguments[t] : {};
    t % 2 ? Ux(Object(r), !0).forEach(function(n) {
      Fu(e, n, r[n]);
    }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(r)) : Ux(Object(r)).forEach(function(n) {
      Object.defineProperty(e, n, Object.getOwnPropertyDescriptor(r, n));
    });
  }
  return e;
}
function Fu(e, t, r) {
  return t = $_(t), t in e ? Object.defineProperty(e, t, { value: r, enumerable: !0, configurable: !0, writable: !0 }) : e[t] = r, e;
}
function $_(e) {
  var t = mB(e, "string");
  return Oi(t) == "symbol" ? t : t + "";
}
function mB(e, t) {
  if (Oi(e) != "object" || !e) return e;
  var r = e[Symbol.toPrimitive];
  if (r !== void 0) {
    var n = r.call(e, t);
    if (Oi(n) != "object") return n;
    throw new TypeError("@@toPrimitive must return a primitive value.");
  }
  return (t === "string" ? String : Number)(e);
}
var R_ = function(t, r, n, a, i) {
  var o = t.width, u = t.height, l = t.layout, s = t.children, f = Object.keys(r), c = {
    left: n.left,
    leftMirror: n.left,
    right: o - n.right,
    rightMirror: o - n.right,
    top: n.top,
    topMirror: n.top,
    bottom: u - n.bottom,
    bottomMirror: u - n.bottom
  }, d = !!vt(s, Bt);
  return f.reduce(function(h, y) {
    var v = r[y], p = v.orientation, g = v.domain, b = v.padding, w = b === void 0 ? {} : b, _ = v.mirror, m = v.reversed, O = "".concat(p).concat(_ ? "Mirror" : ""), x, S, T, C, A;
    if (v.type === "number" && (v.padding === "gap" || v.padding === "no-gap")) {
      var N = g[1] - g[0], $ = 1 / 0, D = v.categoricalDomain.sort();
      if (D.forEach(function(de, ge) {
        ge > 0 && ($ = Math.min((de || 0) - (D[ge - 1] || 0), $));
      }), Number.isFinite($)) {
        var R = $ / N, L = v.layout === "vertical" ? n.height : n.width;
        if (v.padding === "gap" && (x = R * L / 2), v.padding === "no-gap") {
          var z = on(t.barCategoryGap, R * L), F = R * L / 2;
          x = F - z - (F - z) / L * z;
        }
      }
    }
    a === "xAxis" ? S = [n.left + (w.left || 0) + (x || 0), n.left + n.width - (w.right || 0) - (x || 0)] : a === "yAxis" ? S = l === "horizontal" ? [n.top + n.height - (w.bottom || 0), n.top + (w.top || 0)] : [n.top + (w.top || 0) + (x || 0), n.top + n.height - (w.bottom || 0) - (x || 0)] : S = v.range, m && (S = [S[1], S[0]]);
    var W = jI(v, i, d), X = W.scale, J = W.realScaleType;
    X.domain(g).range(S), NI(X);
    var G = qI(X, Rt(Rt({}, v), {}, {
      realScaleType: J
    }));
    a === "xAxis" ? (A = p === "top" && !_ || p === "bottom" && _, T = n.left, C = c[O] - A * v.height) : a === "yAxis" && (A = p === "left" && !_ || p === "right" && _, T = c[O] - A * v.width, C = n.top);
    var Q = Rt(Rt(Rt({}, v), G), {}, {
      realScaleType: J,
      x: T,
      y: C,
      scale: X,
      width: a === "xAxis" ? n.width : v.width,
      height: a === "yAxis" ? n.height : v.height
    });
    return Q.bandSize = Uo(Q, G), !v.hide && a === "xAxis" ? c[O] += (A ? -1 : 1) * Q.height : v.hide || (c[O] += (A ? -1 : 1) * Q.width), Rt(Rt({}, h), {}, Fu({}, y, Q));
  }, {});
}, k_ = function(t, r) {
  var n = t.x, a = t.y, i = r.x, o = r.y;
  return {
    x: Math.min(n, i),
    y: Math.min(a, o),
    width: Math.abs(i - n),
    height: Math.abs(o - a)
  };
}, gB = function(t) {
  var r = t.x1, n = t.y1, a = t.x2, i = t.y2;
  return k_({
    x: r,
    y: n
  }, {
    x: a,
    y: i
  });
}, I_ = /* @__PURE__ */ (function() {
  function e(t) {
    vB(this, e), this.scale = t;
  }
  return yB(e, [{
    key: "domain",
    get: function() {
      return this.scale.domain;
    }
  }, {
    key: "range",
    get: function() {
      return this.scale.range;
    }
  }, {
    key: "rangeMin",
    get: function() {
      return this.range()[0];
    }
  }, {
    key: "rangeMax",
    get: function() {
      return this.range()[1];
    }
  }, {
    key: "bandwidth",
    get: function() {
      return this.scale.bandwidth;
    }
  }, {
    key: "apply",
    value: function(r) {
      var n = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : {}, a = n.bandAware, i = n.position;
      if (r !== void 0) {
        if (i)
          switch (i) {
            case "start":
              return this.scale(r);
            case "middle": {
              var o = this.bandwidth ? this.bandwidth() / 2 : 0;
              return this.scale(r) + o;
            }
            case "end": {
              var u = this.bandwidth ? this.bandwidth() : 0;
              return this.scale(r) + u;
            }
            default:
              return this.scale(r);
          }
        if (a) {
          var l = this.bandwidth ? this.bandwidth() / 2 : 0;
          return this.scale(r) + l;
        }
        return this.scale(r);
      }
    }
  }, {
    key: "isInRange",
    value: function(r) {
      var n = this.range(), a = n[0], i = n[n.length - 1];
      return a <= i ? r >= a && r <= i : r >= i && r <= a;
    }
  }], [{
    key: "create",
    value: function(r) {
      return new e(r);
    }
  }]);
})();
Fu(I_, "EPS", 1e-4);
var Dp = function(t) {
  var r = Object.keys(t).reduce(function(n, a) {
    return Rt(Rt({}, n), {}, Fu({}, a, I_.create(t[a])));
  }, {});
  return Rt(Rt({}, r), {}, {
    apply: function(a) {
      var i = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : {}, o = i.bandAware, u = i.position;
      return G5(a, function(l, s) {
        return r[s].apply(l, {
          bandAware: o,
          position: u
        });
      });
    },
    isInRange: function(a) {
      return M_(a, function(i, o) {
        return r[o].isInRange(i);
      });
    }
  });
};
function bB(e) {
  return (e % 180 + 180) % 180;
}
var xB = function(t) {
  var r = t.width, n = t.height, a = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : 0, i = bB(a), o = i * Math.PI / 180, u = Math.atan(n / r), l = o > u && o < Math.PI - u ? n / Math.sin(o) : r / Math.cos(o);
  return Math.abs(l);
}, Xf, Wx;
function wB() {
  if (Wx) return Xf;
  Wx = 1;
  var e = qr(), t = Ri(), r = Au();
  function n(a) {
    return function(i, o, u) {
      var l = Object(i);
      if (!t(i)) {
        var s = e(o, 3);
        i = r(i), o = function(c) {
          return s(l[c], c, l);
        };
      }
      var f = a(i, o, u);
      return f > -1 ? l[s ? i[f] : f] : void 0;
    };
  }
  return Xf = n, Xf;
}
var Yf, Hx;
function OB() {
  if (Hx) return Yf;
  Hx = 1;
  var e = P_();
  function t(r) {
    var n = e(r), a = n % 1;
    return n === n ? a ? n - a : n : 0;
  }
  return Yf = t, Yf;
}
var Zf, Gx;
function _B() {
  if (Gx) return Zf;
  Gx = 1;
  var e = Vw(), t = qr(), r = OB(), n = Math.max;
  function a(i, o, u) {
    var l = i == null ? 0 : i.length;
    if (!l)
      return -1;
    var s = u == null ? 0 : r(u);
    return s < 0 && (s = n(l + s, 0)), e(i, t(o, 3), s);
  }
  return Zf = a, Zf;
}
var Jf, Kx;
function SB() {
  if (Kx) return Jf;
  Kx = 1;
  var e = wB(), t = _B(), r = e(t);
  return Jf = r, Jf;
}
var PB = SB();
const AB = /* @__PURE__ */ $e(PB);
var EB = cw();
const TB = /* @__PURE__ */ $e(EB);
var MB = TB(function(e) {
  return {
    x: e.left,
    y: e.top,
    width: e.width,
    height: e.height
  };
}, function(e) {
  return ["l", e.left, "t", e.top, "w", e.width, "h", e.height].join("");
}), Lp = /* @__PURE__ */ Ge(void 0), qp = /* @__PURE__ */ Ge(void 0), D_ = /* @__PURE__ */ Ge(void 0), L_ = /* @__PURE__ */ Ge({}), q_ = /* @__PURE__ */ Ge(void 0), B_ = /* @__PURE__ */ Ge(0), F_ = /* @__PURE__ */ Ge(0), Vx = function(t) {
  var r = t.state, n = r.xAxisMap, a = r.yAxisMap, i = r.offset, o = t.clipPathId, u = t.children, l = t.width, s = t.height, f = MB(i);
  return /* @__PURE__ */ M.createElement(Lp.Provider, {
    value: n
  }, /* @__PURE__ */ M.createElement(qp.Provider, {
    value: a
  }, /* @__PURE__ */ M.createElement(L_.Provider, {
    value: i
  }, /* @__PURE__ */ M.createElement(D_.Provider, {
    value: f
  }, /* @__PURE__ */ M.createElement(q_.Provider, {
    value: o
  }, /* @__PURE__ */ M.createElement(B_.Provider, {
    value: s
  }, /* @__PURE__ */ M.createElement(F_.Provider, {
    value: l
  }, u)))))));
}, jB = function() {
  return se(q_);
}, z_ = function(t) {
  var r = se(Lp);
  r == null && ln();
  var n = r[t];
  return n == null && ln(), n;
}, NB = function() {
  var t = se(Lp);
  return Mr(t);
}, CB = function() {
  var t = se(qp), r = AB(t, function(n) {
    return M_(n.domain, Number.isFinite);
  });
  return r || Mr(t);
}, U_ = function(t) {
  var r = se(qp);
  r == null && ln();
  var n = r[t];
  return n == null && ln(), n;
}, $B = function() {
  var t = se(D_);
  return t;
}, RB = function() {
  return se(L_);
}, Bp = function() {
  return se(F_);
}, Fp = function() {
  return se(B_);
};
function Vn(e) {
  "@babel/helpers - typeof";
  return Vn = typeof Symbol == "function" && typeof Symbol.iterator == "symbol" ? function(t) {
    return typeof t;
  } : function(t) {
    return t && typeof Symbol == "function" && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t;
  }, Vn(e);
}
function kB(e, t) {
  if (!(e instanceof t))
    throw new TypeError("Cannot call a class as a function");
}
function IB(e, t) {
  for (var r = 0; r < t.length; r++) {
    var n = t[r];
    n.enumerable = n.enumerable || !1, n.configurable = !0, "value" in n && (n.writable = !0), Object.defineProperty(e, H_(n.key), n);
  }
}
function DB(e, t, r) {
  return t && IB(e.prototype, t), Object.defineProperty(e, "prototype", { writable: !1 }), e;
}
function LB(e, t, r) {
  return t = nu(t), qB(e, W_() ? Reflect.construct(t, r || [], nu(e).constructor) : t.apply(e, r));
}
function qB(e, t) {
  if (t && (Vn(t) === "object" || typeof t == "function"))
    return t;
  if (t !== void 0)
    throw new TypeError("Derived constructors may only return object or undefined");
  return BB(e);
}
function BB(e) {
  if (e === void 0)
    throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
  return e;
}
function W_() {
  try {
    var e = !Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function() {
    }));
  } catch {
  }
  return (W_ = function() {
    return !!e;
  })();
}
function nu(e) {
  return nu = Object.setPrototypeOf ? Object.getPrototypeOf.bind() : function(r) {
    return r.__proto__ || Object.getPrototypeOf(r);
  }, nu(e);
}
function FB(e, t) {
  if (typeof t != "function" && t !== null)
    throw new TypeError("Super expression must either be null or a function");
  e.prototype = Object.create(t && t.prototype, { constructor: { value: e, writable: !0, configurable: !0 } }), Object.defineProperty(e, "prototype", { writable: !1 }), t && sh(e, t);
}
function sh(e, t) {
  return sh = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function(n, a) {
    return n.__proto__ = a, n;
  }, sh(e, t);
}
function Xx(e, t) {
  var r = Object.keys(e);
  if (Object.getOwnPropertySymbols) {
    var n = Object.getOwnPropertySymbols(e);
    t && (n = n.filter(function(a) {
      return Object.getOwnPropertyDescriptor(e, a).enumerable;
    })), r.push.apply(r, n);
  }
  return r;
}
function Yx(e) {
  for (var t = 1; t < arguments.length; t++) {
    var r = arguments[t] != null ? arguments[t] : {};
    t % 2 ? Xx(Object(r), !0).forEach(function(n) {
      zp(e, n, r[n]);
    }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(r)) : Xx(Object(r)).forEach(function(n) {
      Object.defineProperty(e, n, Object.getOwnPropertyDescriptor(r, n));
    });
  }
  return e;
}
function zp(e, t, r) {
  return t = H_(t), t in e ? Object.defineProperty(e, t, { value: r, enumerable: !0, configurable: !0, writable: !0 }) : e[t] = r, e;
}
function H_(e) {
  var t = zB(e, "string");
  return Vn(t) == "symbol" ? t : t + "";
}
function zB(e, t) {
  if (Vn(e) != "object" || !e) return e;
  var r = e[Symbol.toPrimitive];
  if (r !== void 0) {
    var n = r.call(e, t);
    if (Vn(n) != "object") return n;
    throw new TypeError("@@toPrimitive must return a primitive value.");
  }
  return String(e);
}
function UB(e, t) {
  return KB(e) || GB(e, t) || HB(e, t) || WB();
}
function WB() {
  throw new TypeError(`Invalid attempt to destructure non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`);
}
function HB(e, t) {
  if (e) {
    if (typeof e == "string") return Zx(e, t);
    var r = Object.prototype.toString.call(e).slice(8, -1);
    if (r === "Object" && e.constructor && (r = e.constructor.name), r === "Map" || r === "Set") return Array.from(e);
    if (r === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r)) return Zx(e, t);
  }
}
function Zx(e, t) {
  (t == null || t > e.length) && (t = e.length);
  for (var r = 0, n = new Array(t); r < t; r++) n[r] = e[r];
  return n;
}
function GB(e, t) {
  var r = e == null ? null : typeof Symbol < "u" && e[Symbol.iterator] || e["@@iterator"];
  if (r != null) {
    var n, a, i, o, u = [], l = !0, s = !1;
    try {
      if (i = (r = r.call(e)).next, t !== 0) for (; !(l = (n = i.call(r)).done) && (u.push(n.value), u.length !== t); l = !0) ;
    } catch (f) {
      s = !0, a = f;
    } finally {
      try {
        if (!l && r.return != null && (o = r.return(), Object(o) !== o)) return;
      } finally {
        if (s) throw a;
      }
    }
    return u;
  }
}
function KB(e) {
  if (Array.isArray(e)) return e;
}
function ch() {
  return ch = Object.assign ? Object.assign.bind() : function(e) {
    for (var t = 1; t < arguments.length; t++) {
      var r = arguments[t];
      for (var n in r)
        Object.prototype.hasOwnProperty.call(r, n) && (e[n] = r[n]);
    }
    return e;
  }, ch.apply(this, arguments);
}
var VB = function(t, r) {
  var n;
  return /* @__PURE__ */ M.isValidElement(t) ? n = /* @__PURE__ */ M.cloneElement(t, r) : fe(t) ? n = t(r) : n = /* @__PURE__ */ M.createElement("line", ch({}, r, {
    className: "recharts-reference-line-line"
  })), n;
}, XB = function(t, r, n, a, i, o, u, l, s) {
  var f = i.x, c = i.y, d = i.width, h = i.height;
  if (n) {
    var y = s.y, v = t.y.apply(y, {
      position: o
    });
    if (Xt(s, "discard") && !t.y.isInRange(v))
      return null;
    var p = [{
      x: f + d,
      y: v
    }, {
      x: f,
      y: v
    }];
    return l === "left" ? p.reverse() : p;
  }
  if (r) {
    var g = s.x, b = t.x.apply(g, {
      position: o
    });
    if (Xt(s, "discard") && !t.x.isInRange(b))
      return null;
    var w = [{
      x: b,
      y: c + h
    }, {
      x: b,
      y: c
    }];
    return u === "top" ? w.reverse() : w;
  }
  if (a) {
    var _ = s.segment, m = _.map(function(O) {
      return t.apply(O, {
        position: o
      });
    });
    return Xt(s, "discard") && z5(m, function(O) {
      return !t.isInRange(O);
    }) ? null : m;
  }
  return null;
};
function YB(e) {
  var t = e.x, r = e.y, n = e.segment, a = e.xAxisId, i = e.yAxisId, o = e.shape, u = e.className, l = e.alwaysShow, s = jB(), f = z_(a), c = U_(i), d = $B();
  if (!s || !d)
    return null;
  dr(l === void 0, 'The alwaysShow prop is deprecated. Please use ifOverflow="extendDomain" instead.');
  var h = Dp({
    x: f.scale,
    y: c.scale
  }), y = Ve(t), v = Ve(r), p = n && n.length === 2, g = XB(h, y, v, p, d, e.position, f.orientation, c.orientation, e);
  if (!g)
    return null;
  var b = UB(g, 2), w = b[0], _ = w.x, m = w.y, O = b[1], x = O.x, S = O.y, T = Xt(e, "hidden") ? "url(#".concat(s, ")") : void 0, C = Yx(Yx({
    clipPath: T
  }, pe(e, !0)), {}, {
    x1: _,
    y1: m,
    x2: x,
    y2: S
  });
  return /* @__PURE__ */ M.createElement(Ie, {
    className: _e("recharts-reference-line", u)
  }, VB(o, C), ot.renderCallByParent(e, gB({
    x1: _,
    y1: m,
    x2: x,
    y2: S
  })));
}
var Up = /* @__PURE__ */ (function(e) {
  function t() {
    return kB(this, t), LB(this, t, arguments);
  }
  return FB(t, e), DB(t, [{
    key: "render",
    value: function() {
      return /* @__PURE__ */ M.createElement(YB, this.props);
    }
  }]);
})(M.Component);
zp(Up, "displayName", "ReferenceLine");
zp(Up, "defaultProps", {
  isFront: !1,
  ifOverflow: "discard",
  xAxisId: 0,
  yAxisId: 0,
  fill: "none",
  stroke: "#ccc",
  fillOpacity: 1,
  strokeWidth: 1,
  position: "middle"
});
function fh() {
  return fh = Object.assign ? Object.assign.bind() : function(e) {
    for (var t = 1; t < arguments.length; t++) {
      var r = arguments[t];
      for (var n in r)
        Object.prototype.hasOwnProperty.call(r, n) && (e[n] = r[n]);
    }
    return e;
  }, fh.apply(this, arguments);
}
function Xn(e) {
  "@babel/helpers - typeof";
  return Xn = typeof Symbol == "function" && typeof Symbol.iterator == "symbol" ? function(t) {
    return typeof t;
  } : function(t) {
    return t && typeof Symbol == "function" && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t;
  }, Xn(e);
}
function Jx(e, t) {
  var r = Object.keys(e);
  if (Object.getOwnPropertySymbols) {
    var n = Object.getOwnPropertySymbols(e);
    t && (n = n.filter(function(a) {
      return Object.getOwnPropertyDescriptor(e, a).enumerable;
    })), r.push.apply(r, n);
  }
  return r;
}
function Qx(e) {
  for (var t = 1; t < arguments.length; t++) {
    var r = arguments[t] != null ? arguments[t] : {};
    t % 2 ? Jx(Object(r), !0).forEach(function(n) {
      zu(e, n, r[n]);
    }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(r)) : Jx(Object(r)).forEach(function(n) {
      Object.defineProperty(e, n, Object.getOwnPropertyDescriptor(r, n));
    });
  }
  return e;
}
function ZB(e, t) {
  if (!(e instanceof t))
    throw new TypeError("Cannot call a class as a function");
}
function JB(e, t) {
  for (var r = 0; r < t.length; r++) {
    var n = t[r];
    n.enumerable = n.enumerable || !1, n.configurable = !0, "value" in n && (n.writable = !0), Object.defineProperty(e, K_(n.key), n);
  }
}
function QB(e, t, r) {
  return t && JB(e.prototype, t), Object.defineProperty(e, "prototype", { writable: !1 }), e;
}
function e3(e, t, r) {
  return t = au(t), t3(e, G_() ? Reflect.construct(t, r || [], au(e).constructor) : t.apply(e, r));
}
function t3(e, t) {
  if (t && (Xn(t) === "object" || typeof t == "function"))
    return t;
  if (t !== void 0)
    throw new TypeError("Derived constructors may only return object or undefined");
  return r3(e);
}
function r3(e) {
  if (e === void 0)
    throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
  return e;
}
function G_() {
  try {
    var e = !Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function() {
    }));
  } catch {
  }
  return (G_ = function() {
    return !!e;
  })();
}
function au(e) {
  return au = Object.setPrototypeOf ? Object.getPrototypeOf.bind() : function(r) {
    return r.__proto__ || Object.getPrototypeOf(r);
  }, au(e);
}
function n3(e, t) {
  if (typeof t != "function" && t !== null)
    throw new TypeError("Super expression must either be null or a function");
  e.prototype = Object.create(t && t.prototype, { constructor: { value: e, writable: !0, configurable: !0 } }), Object.defineProperty(e, "prototype", { writable: !1 }), t && dh(e, t);
}
function dh(e, t) {
  return dh = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function(n, a) {
    return n.__proto__ = a, n;
  }, dh(e, t);
}
function zu(e, t, r) {
  return t = K_(t), t in e ? Object.defineProperty(e, t, { value: r, enumerable: !0, configurable: !0, writable: !0 }) : e[t] = r, e;
}
function K_(e) {
  var t = a3(e, "string");
  return Xn(t) == "symbol" ? t : t + "";
}
function a3(e, t) {
  if (Xn(e) != "object" || !e) return e;
  var r = e[Symbol.toPrimitive];
  if (r !== void 0) {
    var n = r.call(e, t);
    if (Xn(n) != "object") return n;
    throw new TypeError("@@toPrimitive must return a primitive value.");
  }
  return String(e);
}
var i3 = function(t) {
  var r = t.x, n = t.y, a = t.xAxis, i = t.yAxis, o = Dp({
    x: a.scale,
    y: i.scale
  }), u = o.apply({
    x: r,
    y: n
  }, {
    bandAware: !0
  });
  return Xt(t, "discard") && !o.isInRange(u) ? null : u;
}, Uu = /* @__PURE__ */ (function(e) {
  function t() {
    return ZB(this, t), e3(this, t, arguments);
  }
  return n3(t, e), QB(t, [{
    key: "render",
    value: function() {
      var n = this.props, a = n.x, i = n.y, o = n.r, u = n.alwaysShow, l = n.clipPathId, s = Ve(a), f = Ve(i);
      if (dr(u === void 0, 'The alwaysShow prop is deprecated. Please use ifOverflow="extendDomain" instead.'), !s || !f)
        return null;
      var c = i3(this.props);
      if (!c)
        return null;
      var d = c.x, h = c.y, y = this.props, v = y.shape, p = y.className, g = Xt(this.props, "hidden") ? "url(#".concat(l, ")") : void 0, b = Qx(Qx({
        clipPath: g
      }, pe(this.props, !0)), {}, {
        cx: d,
        cy: h
      });
      return /* @__PURE__ */ M.createElement(Ie, {
        className: _e("recharts-reference-dot", p)
      }, t.renderDot(v, b), ot.renderCallByParent(this.props, {
        x: d - o,
        y: h - o,
        width: 2 * o,
        height: 2 * o
      }));
    }
  }]);
})(M.Component);
zu(Uu, "displayName", "ReferenceDot");
zu(Uu, "defaultProps", {
  isFront: !1,
  ifOverflow: "discard",
  xAxisId: 0,
  yAxisId: 0,
  r: 10,
  fill: "#fff",
  stroke: "#ccc",
  fillOpacity: 1,
  strokeWidth: 1
});
zu(Uu, "renderDot", function(e, t) {
  var r;
  return /* @__PURE__ */ M.isValidElement(e) ? r = /* @__PURE__ */ M.cloneElement(e, t) : fe(e) ? r = e(t) : r = /* @__PURE__ */ M.createElement(Ip, fh({}, t, {
    cx: t.cx,
    cy: t.cy,
    className: "recharts-reference-dot-dot"
  })), r;
});
function hh() {
  return hh = Object.assign ? Object.assign.bind() : function(e) {
    for (var t = 1; t < arguments.length; t++) {
      var r = arguments[t];
      for (var n in r)
        Object.prototype.hasOwnProperty.call(r, n) && (e[n] = r[n]);
    }
    return e;
  }, hh.apply(this, arguments);
}
function Yn(e) {
  "@babel/helpers - typeof";
  return Yn = typeof Symbol == "function" && typeof Symbol.iterator == "symbol" ? function(t) {
    return typeof t;
  } : function(t) {
    return t && typeof Symbol == "function" && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t;
  }, Yn(e);
}
function e1(e, t) {
  var r = Object.keys(e);
  if (Object.getOwnPropertySymbols) {
    var n = Object.getOwnPropertySymbols(e);
    t && (n = n.filter(function(a) {
      return Object.getOwnPropertyDescriptor(e, a).enumerable;
    })), r.push.apply(r, n);
  }
  return r;
}
function t1(e) {
  for (var t = 1; t < arguments.length; t++) {
    var r = arguments[t] != null ? arguments[t] : {};
    t % 2 ? e1(Object(r), !0).forEach(function(n) {
      Wu(e, n, r[n]);
    }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(r)) : e1(Object(r)).forEach(function(n) {
      Object.defineProperty(e, n, Object.getOwnPropertyDescriptor(r, n));
    });
  }
  return e;
}
function o3(e, t) {
  if (!(e instanceof t))
    throw new TypeError("Cannot call a class as a function");
}
function u3(e, t) {
  for (var r = 0; r < t.length; r++) {
    var n = t[r];
    n.enumerable = n.enumerable || !1, n.configurable = !0, "value" in n && (n.writable = !0), Object.defineProperty(e, X_(n.key), n);
  }
}
function l3(e, t, r) {
  return t && u3(e.prototype, t), Object.defineProperty(e, "prototype", { writable: !1 }), e;
}
function s3(e, t, r) {
  return t = iu(t), c3(e, V_() ? Reflect.construct(t, r || [], iu(e).constructor) : t.apply(e, r));
}
function c3(e, t) {
  if (t && (Yn(t) === "object" || typeof t == "function"))
    return t;
  if (t !== void 0)
    throw new TypeError("Derived constructors may only return object or undefined");
  return f3(e);
}
function f3(e) {
  if (e === void 0)
    throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
  return e;
}
function V_() {
  try {
    var e = !Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function() {
    }));
  } catch {
  }
  return (V_ = function() {
    return !!e;
  })();
}
function iu(e) {
  return iu = Object.setPrototypeOf ? Object.getPrototypeOf.bind() : function(r) {
    return r.__proto__ || Object.getPrototypeOf(r);
  }, iu(e);
}
function d3(e, t) {
  if (typeof t != "function" && t !== null)
    throw new TypeError("Super expression must either be null or a function");
  e.prototype = Object.create(t && t.prototype, { constructor: { value: e, writable: !0, configurable: !0 } }), Object.defineProperty(e, "prototype", { writable: !1 }), t && ph(e, t);
}
function ph(e, t) {
  return ph = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function(n, a) {
    return n.__proto__ = a, n;
  }, ph(e, t);
}
function Wu(e, t, r) {
  return t = X_(t), t in e ? Object.defineProperty(e, t, { value: r, enumerable: !0, configurable: !0, writable: !0 }) : e[t] = r, e;
}
function X_(e) {
  var t = h3(e, "string");
  return Yn(t) == "symbol" ? t : t + "";
}
function h3(e, t) {
  if (Yn(e) != "object" || !e) return e;
  var r = e[Symbol.toPrimitive];
  if (r !== void 0) {
    var n = r.call(e, t);
    if (Yn(n) != "object") return n;
    throw new TypeError("@@toPrimitive must return a primitive value.");
  }
  return String(e);
}
var p3 = function(t, r, n, a, i) {
  var o = i.x1, u = i.x2, l = i.y1, s = i.y2, f = i.xAxis, c = i.yAxis;
  if (!f || !c) return null;
  var d = Dp({
    x: f.scale,
    y: c.scale
  }), h = {
    x: t ? d.x.apply(o, {
      position: "start"
    }) : d.x.rangeMin,
    y: n ? d.y.apply(l, {
      position: "start"
    }) : d.y.rangeMin
  }, y = {
    x: r ? d.x.apply(u, {
      position: "end"
    }) : d.x.rangeMax,
    y: a ? d.y.apply(s, {
      position: "end"
    }) : d.y.rangeMax
  };
  return Xt(i, "discard") && (!d.isInRange(h) || !d.isInRange(y)) ? null : k_(h, y);
}, Hu = /* @__PURE__ */ (function(e) {
  function t() {
    return o3(this, t), s3(this, t, arguments);
  }
  return d3(t, e), l3(t, [{
    key: "render",
    value: function() {
      var n = this.props, a = n.x1, i = n.x2, o = n.y1, u = n.y2, l = n.className, s = n.alwaysShow, f = n.clipPathId;
      dr(s === void 0, 'The alwaysShow prop is deprecated. Please use ifOverflow="extendDomain" instead.');
      var c = Ve(a), d = Ve(i), h = Ve(o), y = Ve(u), v = this.props.shape;
      if (!c && !d && !h && !y && !v)
        return null;
      var p = p3(c, d, h, y, this.props);
      if (!p && !v)
        return null;
      var g = Xt(this.props, "hidden") ? "url(#".concat(f, ")") : void 0;
      return /* @__PURE__ */ M.createElement(Ie, {
        className: _e("recharts-reference-area", l)
      }, t.renderRect(v, t1(t1({
        clipPath: g
      }, pe(this.props, !0)), p)), ot.renderCallByParent(this.props, p));
    }
  }]);
})(M.Component);
Wu(Hu, "displayName", "ReferenceArea");
Wu(Hu, "defaultProps", {
  isFront: !1,
  ifOverflow: "discard",
  xAxisId: 0,
  yAxisId: 0,
  r: 10,
  fill: "#ccc",
  fillOpacity: 0.5,
  stroke: "none",
  strokeWidth: 1
});
Wu(Hu, "renderRect", function(e, t) {
  var r;
  return /* @__PURE__ */ M.isValidElement(e) ? r = /* @__PURE__ */ M.cloneElement(e, t) : fe(e) ? r = e(t) : r = /* @__PURE__ */ M.createElement(kp, hh({}, t, {
    className: "recharts-reference-area-rect"
  })), r;
});
function Y_(e, t, r) {
  if (t < 1)
    return [];
  if (t === 1 && r === void 0)
    return e;
  for (var n = [], a = 0; a < e.length; a += t)
    n.push(e[a]);
  return n;
}
function v3(e, t, r) {
  var n = {
    width: e.width + t.width,
    height: e.height + t.height
  };
  return xB(n, r);
}
function y3(e, t, r) {
  var n = r === "width", a = e.x, i = e.y, o = e.width, u = e.height;
  return t === 1 ? {
    start: n ? a : i,
    end: n ? a + o : i + u
  } : {
    start: n ? a + o : i + u,
    end: n ? a : i
  };
}
function ou(e, t, r, n, a) {
  if (e * t < e * n || e * t > e * a)
    return !1;
  var i = r();
  return e * (t - e * i / 2 - n) >= 0 && e * (t + e * i / 2 - a) <= 0;
}
function m3(e, t) {
  return Y_(e, t + 1);
}
function g3(e, t, r, n, a) {
  for (var i = (n || []).slice(), o = t.start, u = t.end, l = 0, s = 1, f = o, c = function() {
    var y = n?.[l];
    if (y === void 0)
      return {
        v: Y_(n, s)
      };
    var v = l, p, g = function() {
      return p === void 0 && (p = r(y, v)), p;
    }, b = y.coordinate, w = l === 0 || ou(e, b, g, f, u);
    w || (l = 0, f = o, s += 1), w && (f = b + e * (g() / 2 + a), l += s);
  }, d; s <= i.length; )
    if (d = c(), d) return d.v;
  return [];
}
function _i(e) {
  "@babel/helpers - typeof";
  return _i = typeof Symbol == "function" && typeof Symbol.iterator == "symbol" ? function(t) {
    return typeof t;
  } : function(t) {
    return t && typeof Symbol == "function" && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t;
  }, _i(e);
}
function r1(e, t) {
  var r = Object.keys(e);
  if (Object.getOwnPropertySymbols) {
    var n = Object.getOwnPropertySymbols(e);
    t && (n = n.filter(function(a) {
      return Object.getOwnPropertyDescriptor(e, a).enumerable;
    })), r.push.apply(r, n);
  }
  return r;
}
function at(e) {
  for (var t = 1; t < arguments.length; t++) {
    var r = arguments[t] != null ? arguments[t] : {};
    t % 2 ? r1(Object(r), !0).forEach(function(n) {
      b3(e, n, r[n]);
    }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(r)) : r1(Object(r)).forEach(function(n) {
      Object.defineProperty(e, n, Object.getOwnPropertyDescriptor(r, n));
    });
  }
  return e;
}
function b3(e, t, r) {
  return t = x3(t), t in e ? Object.defineProperty(e, t, { value: r, enumerable: !0, configurable: !0, writable: !0 }) : e[t] = r, e;
}
function x3(e) {
  var t = w3(e, "string");
  return _i(t) == "symbol" ? t : t + "";
}
function w3(e, t) {
  if (_i(e) != "object" || !e) return e;
  var r = e[Symbol.toPrimitive];
  if (r !== void 0) {
    var n = r.call(e, t);
    if (_i(n) != "object") return n;
    throw new TypeError("@@toPrimitive must return a primitive value.");
  }
  return (t === "string" ? String : Number)(e);
}
function O3(e, t, r, n, a) {
  for (var i = (n || []).slice(), o = i.length, u = t.start, l = t.end, s = function(d) {
    var h = i[d], y, v = function() {
      return y === void 0 && (y = r(h, d)), y;
    };
    if (d === o - 1) {
      var p = e * (h.coordinate + e * v() / 2 - l);
      i[d] = h = at(at({}, h), {}, {
        tickCoord: p > 0 ? h.coordinate - p * e : h.coordinate
      });
    } else
      i[d] = h = at(at({}, h), {}, {
        tickCoord: h.coordinate
      });
    var g = ou(e, h.tickCoord, v, u, l);
    g && (l = h.tickCoord - e * (v() / 2 + a), i[d] = at(at({}, h), {}, {
      isShow: !0
    }));
  }, f = o - 1; f >= 0; f--)
    s(f);
  return i;
}
function _3(e, t, r, n, a, i) {
  var o = (n || []).slice(), u = o.length, l = t.start, s = t.end;
  if (i) {
    var f = n[u - 1], c = r(f, u - 1), d = e * (f.coordinate + e * c / 2 - s);
    o[u - 1] = f = at(at({}, f), {}, {
      tickCoord: d > 0 ? f.coordinate - d * e : f.coordinate
    });
    var h = ou(e, f.tickCoord, function() {
      return c;
    }, l, s);
    h && (s = f.tickCoord - e * (c / 2 + a), o[u - 1] = at(at({}, f), {}, {
      isShow: !0
    }));
  }
  for (var y = i ? u - 1 : u, v = function(b) {
    var w = o[b], _, m = function() {
      return _ === void 0 && (_ = r(w, b)), _;
    };
    if (b === 0) {
      var O = e * (w.coordinate - e * m() / 2 - l);
      o[b] = w = at(at({}, w), {}, {
        tickCoord: O < 0 ? w.coordinate - O * e : w.coordinate
      });
    } else
      o[b] = w = at(at({}, w), {}, {
        tickCoord: w.coordinate
      });
    var x = ou(e, w.tickCoord, m, l, s);
    x && (l = w.tickCoord + e * (m() / 2 + a), o[b] = at(at({}, w), {}, {
      isShow: !0
    }));
  }, p = 0; p < y; p++)
    v(p);
  return o;
}
function Wp(e, t, r) {
  var n = e.tick, a = e.ticks, i = e.viewBox, o = e.minTickGap, u = e.orientation, l = e.interval, s = e.tickFormatter, f = e.unit, c = e.angle;
  if (!a || !a.length || !n)
    return [];
  if (H(l) || ua.isSsr)
    return m3(a, typeof l == "number" && H(l) ? l : 0);
  var d = [], h = u === "top" || u === "bottom" ? "width" : "height", y = f && h === "width" ? ka(f, {
    fontSize: t,
    letterSpacing: r
  }) : {
    width: 0,
    height: 0
  }, v = function(w, _) {
    var m = fe(s) ? s(w.value, _) : w.value;
    return h === "width" ? v3(ka(m, {
      fontSize: t,
      letterSpacing: r
    }), y, c) : ka(m, {
      fontSize: t,
      letterSpacing: r
    })[h];
  }, p = a.length >= 2 ? Dt(a[1].coordinate - a[0].coordinate) : 1, g = y3(i, p, h);
  return l === "equidistantPreserveStart" ? g3(p, g, v, a, o) : (l === "preserveStart" || l === "preserveStartEnd" ? d = _3(p, g, v, a, o, l === "preserveStartEnd") : d = O3(p, g, v, a, o), d.filter(function(b) {
    return b.isShow;
  }));
}
var S3 = ["viewBox"], P3 = ["viewBox"], A3 = ["ticks"];
function Zn(e) {
  "@babel/helpers - typeof";
  return Zn = typeof Symbol == "function" && typeof Symbol.iterator == "symbol" ? function(t) {
    return typeof t;
  } : function(t) {
    return t && typeof Symbol == "function" && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t;
  }, Zn(e);
}
function Tn() {
  return Tn = Object.assign ? Object.assign.bind() : function(e) {
    for (var t = 1; t < arguments.length; t++) {
      var r = arguments[t];
      for (var n in r)
        Object.prototype.hasOwnProperty.call(r, n) && (e[n] = r[n]);
    }
    return e;
  }, Tn.apply(this, arguments);
}
function n1(e, t) {
  var r = Object.keys(e);
  if (Object.getOwnPropertySymbols) {
    var n = Object.getOwnPropertySymbols(e);
    t && (n = n.filter(function(a) {
      return Object.getOwnPropertyDescriptor(e, a).enumerable;
    })), r.push.apply(r, n);
  }
  return r;
}
function st(e) {
  for (var t = 1; t < arguments.length; t++) {
    var r = arguments[t] != null ? arguments[t] : {};
    t % 2 ? n1(Object(r), !0).forEach(function(n) {
      Hp(e, n, r[n]);
    }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(r)) : n1(Object(r)).forEach(function(n) {
      Object.defineProperty(e, n, Object.getOwnPropertyDescriptor(r, n));
    });
  }
  return e;
}
function Qf(e, t) {
  if (e == null) return {};
  var r = E3(e, t), n, a;
  if (Object.getOwnPropertySymbols) {
    var i = Object.getOwnPropertySymbols(e);
    for (a = 0; a < i.length; a++)
      n = i[a], !(t.indexOf(n) >= 0) && Object.prototype.propertyIsEnumerable.call(e, n) && (r[n] = e[n]);
  }
  return r;
}
function E3(e, t) {
  if (e == null) return {};
  var r = {};
  for (var n in e)
    if (Object.prototype.hasOwnProperty.call(e, n)) {
      if (t.indexOf(n) >= 0) continue;
      r[n] = e[n];
    }
  return r;
}
function T3(e, t) {
  if (!(e instanceof t))
    throw new TypeError("Cannot call a class as a function");
}
function a1(e, t) {
  for (var r = 0; r < t.length; r++) {
    var n = t[r];
    n.enumerable = n.enumerable || !1, n.configurable = !0, "value" in n && (n.writable = !0), Object.defineProperty(e, J_(n.key), n);
  }
}
function M3(e, t, r) {
  return t && a1(e.prototype, t), r && a1(e, r), Object.defineProperty(e, "prototype", { writable: !1 }), e;
}
function j3(e, t, r) {
  return t = uu(t), N3(e, Z_() ? Reflect.construct(t, r || [], uu(e).constructor) : t.apply(e, r));
}
function N3(e, t) {
  if (t && (Zn(t) === "object" || typeof t == "function"))
    return t;
  if (t !== void 0)
    throw new TypeError("Derived constructors may only return object or undefined");
  return C3(e);
}
function C3(e) {
  if (e === void 0)
    throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
  return e;
}
function Z_() {
  try {
    var e = !Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function() {
    }));
  } catch {
  }
  return (Z_ = function() {
    return !!e;
  })();
}
function uu(e) {
  return uu = Object.setPrototypeOf ? Object.getPrototypeOf.bind() : function(r) {
    return r.__proto__ || Object.getPrototypeOf(r);
  }, uu(e);
}
function $3(e, t) {
  if (typeof t != "function" && t !== null)
    throw new TypeError("Super expression must either be null or a function");
  e.prototype = Object.create(t && t.prototype, { constructor: { value: e, writable: !0, configurable: !0 } }), Object.defineProperty(e, "prototype", { writable: !1 }), t && vh(e, t);
}
function vh(e, t) {
  return vh = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function(n, a) {
    return n.__proto__ = a, n;
  }, vh(e, t);
}
function Hp(e, t, r) {
  return t = J_(t), t in e ? Object.defineProperty(e, t, { value: r, enumerable: !0, configurable: !0, writable: !0 }) : e[t] = r, e;
}
function J_(e) {
  var t = R3(e, "string");
  return Zn(t) == "symbol" ? t : t + "";
}
function R3(e, t) {
  if (Zn(e) != "object" || !e) return e;
  var r = e[Symbol.toPrimitive];
  if (r !== void 0) {
    var n = r.call(e, t);
    if (Zn(n) != "object") return n;
    throw new TypeError("@@toPrimitive must return a primitive value.");
  }
  return String(e);
}
var fa = /* @__PURE__ */ (function(e) {
  function t(r) {
    var n;
    return T3(this, t), n = j3(this, t, [r]), n.state = {
      fontSize: "",
      letterSpacing: ""
    }, n;
  }
  return $3(t, e), M3(t, [{
    key: "shouldComponentUpdate",
    value: function(n, a) {
      var i = n.viewBox, o = Qf(n, S3), u = this.props, l = u.viewBox, s = Qf(u, P3);
      return !Nn(i, l) || !Nn(o, s) || !Nn(a, this.state);
    }
  }, {
    key: "componentDidMount",
    value: function() {
      var n = this.layerReference;
      if (n) {
        var a = n.getElementsByClassName("recharts-cartesian-axis-tick-value")[0];
        a && this.setState({
          fontSize: window.getComputedStyle(a).fontSize,
          letterSpacing: window.getComputedStyle(a).letterSpacing
        });
      }
    }
    /**
     * Calculate the coordinates of endpoints in ticks
     * @param  {Object} data The data of a simple tick
     * @return {Object} (x1, y1): The coordinate of endpoint close to tick text
     *  (x2, y2): The coordinate of endpoint close to axis
     */
  }, {
    key: "getTickLineCoord",
    value: function(n) {
      var a = this.props, i = a.x, o = a.y, u = a.width, l = a.height, s = a.orientation, f = a.tickSize, c = a.mirror, d = a.tickMargin, h, y, v, p, g, b, w = c ? -1 : 1, _ = n.tickSize || f, m = H(n.tickCoord) ? n.tickCoord : n.coordinate;
      switch (s) {
        case "top":
          h = y = n.coordinate, p = o + +!c * l, v = p - w * _, b = v - w * d, g = m;
          break;
        case "left":
          v = p = n.coordinate, y = i + +!c * u, h = y - w * _, g = h - w * d, b = m;
          break;
        case "right":
          v = p = n.coordinate, y = i + +c * u, h = y + w * _, g = h + w * d, b = m;
          break;
        default:
          h = y = n.coordinate, p = o + +c * l, v = p + w * _, b = v + w * d, g = m;
          break;
      }
      return {
        line: {
          x1: h,
          y1: v,
          x2: y,
          y2: p
        },
        tick: {
          x: g,
          y: b
        }
      };
    }
  }, {
    key: "getTickTextAnchor",
    value: function() {
      var n = this.props, a = n.orientation, i = n.mirror, o;
      switch (a) {
        case "left":
          o = i ? "start" : "end";
          break;
        case "right":
          o = i ? "end" : "start";
          break;
        default:
          o = "middle";
          break;
      }
      return o;
    }
  }, {
    key: "getTickVerticalAnchor",
    value: function() {
      var n = this.props, a = n.orientation, i = n.mirror, o = "end";
      switch (a) {
        case "left":
        case "right":
          o = "middle";
          break;
        case "top":
          o = i ? "start" : "end";
          break;
        default:
          o = i ? "end" : "start";
          break;
      }
      return o;
    }
  }, {
    key: "renderAxisLine",
    value: function() {
      var n = this.props, a = n.x, i = n.y, o = n.width, u = n.height, l = n.orientation, s = n.mirror, f = n.axisLine, c = st(st(st({}, pe(this.props, !1)), pe(f, !1)), {}, {
        fill: "none"
      });
      if (l === "top" || l === "bottom") {
        var d = +(l === "top" && !s || l === "bottom" && s);
        c = st(st({}, c), {}, {
          x1: a,
          y1: i + d * u,
          x2: a + o,
          y2: i + d * u
        });
      } else {
        var h = +(l === "left" && !s || l === "right" && s);
        c = st(st({}, c), {}, {
          x1: a + h * o,
          y1: i,
          x2: a + h * o,
          y2: i + u
        });
      }
      return /* @__PURE__ */ M.createElement("line", Tn({}, c, {
        className: _e("recharts-cartesian-axis-line", Tt(f, "className"))
      }));
    }
  }, {
    key: "renderTicks",
    value: (
      /**
       * render the ticks
       * @param {Array} ticks The ticks to actually render (overrides what was passed in props)
       * @param {string} fontSize Fontsize to consider for tick spacing
       * @param {string} letterSpacing Letterspacing to consider for tick spacing
       * @return {ReactComponent} renderedTicks
       */
      function(n, a, i) {
        var o = this, u = this.props, l = u.tickLine, s = u.stroke, f = u.tick, c = u.tickFormatter, d = u.unit, h = Wp(st(st({}, this.props), {}, {
          ticks: n
        }), a, i), y = this.getTickTextAnchor(), v = this.getTickVerticalAnchor(), p = pe(this.props, !1), g = pe(f, !1), b = st(st({}, p), {}, {
          fill: "none"
        }, pe(l, !1)), w = h.map(function(_, m) {
          var O = o.getTickLineCoord(_), x = O.line, S = O.tick, T = st(st(st(st({
            textAnchor: y,
            verticalAnchor: v
          }, p), {}, {
            stroke: "none",
            fill: s
          }, g), S), {}, {
            index: m,
            payload: _,
            visibleTicksCount: h.length,
            tickFormatter: c
          });
          return /* @__PURE__ */ M.createElement(Ie, Tn({
            className: "recharts-cartesian-axis-tick",
            key: "tick-".concat(_.value, "-").concat(_.coordinate, "-").concat(_.tickCoord)
          }, go(o.props, _, m)), l && /* @__PURE__ */ M.createElement("line", Tn({}, b, x, {
            className: _e("recharts-cartesian-axis-tick-line", Tt(l, "className"))
          })), f && t.renderTickItem(f, T, "".concat(fe(c) ? c(_.value, m) : _.value).concat(d || "")));
        });
        return /* @__PURE__ */ M.createElement("g", {
          className: "recharts-cartesian-axis-ticks"
        }, w);
      }
    )
  }, {
    key: "render",
    value: function() {
      var n = this, a = this.props, i = a.axisLine, o = a.width, u = a.height, l = a.ticksGenerator, s = a.className, f = a.hide;
      if (f)
        return null;
      var c = this.props, d = c.ticks, h = Qf(c, A3), y = d;
      return fe(l) && (y = d && d.length > 0 ? l(this.props) : l(h)), o <= 0 || u <= 0 || !y || !y.length ? null : /* @__PURE__ */ M.createElement(Ie, {
        className: _e("recharts-cartesian-axis", s),
        ref: function(p) {
          n.layerReference = p;
        }
      }, i && this.renderAxisLine(), this.renderTicks(y, this.state.fontSize, this.state.letterSpacing), ot.renderCallByParent(this.props));
    }
  }], [{
    key: "renderTickItem",
    value: function(n, a, i) {
      var o;
      return /* @__PURE__ */ M.isValidElement(n) ? o = /* @__PURE__ */ M.cloneElement(n, a) : fe(n) ? o = n(a) : o = /* @__PURE__ */ M.createElement(Mo, Tn({}, a, {
        className: "recharts-cartesian-axis-tick-value"
      }), i), o;
    }
  }]);
})(hu);
Hp(fa, "displayName", "CartesianAxis");
Hp(fa, "defaultProps", {
  x: 0,
  y: 0,
  width: 0,
  height: 0,
  viewBox: {
    x: 0,
    y: 0,
    width: 0,
    height: 0
  },
  // The orientation of axis
  orientation: "bottom",
  // The ticks
  ticks: [],
  stroke: "#666",
  tickLine: !0,
  axisLine: !0,
  tick: !0,
  mirror: !1,
  minTickGap: 5,
  // The width or height of tick
  tickSize: 6,
  tickMargin: 2,
  interval: "preserveEnd"
});
var k3 = ["x1", "y1", "x2", "y2", "key"], I3 = ["offset"];
function sn(e) {
  "@babel/helpers - typeof";
  return sn = typeof Symbol == "function" && typeof Symbol.iterator == "symbol" ? function(t) {
    return typeof t;
  } : function(t) {
    return t && typeof Symbol == "function" && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t;
  }, sn(e);
}
function i1(e, t) {
  var r = Object.keys(e);
  if (Object.getOwnPropertySymbols) {
    var n = Object.getOwnPropertySymbols(e);
    t && (n = n.filter(function(a) {
      return Object.getOwnPropertyDescriptor(e, a).enumerable;
    })), r.push.apply(r, n);
  }
  return r;
}
function ut(e) {
  for (var t = 1; t < arguments.length; t++) {
    var r = arguments[t] != null ? arguments[t] : {};
    t % 2 ? i1(Object(r), !0).forEach(function(n) {
      D3(e, n, r[n]);
    }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(r)) : i1(Object(r)).forEach(function(n) {
      Object.defineProperty(e, n, Object.getOwnPropertyDescriptor(r, n));
    });
  }
  return e;
}
function D3(e, t, r) {
  return t = L3(t), t in e ? Object.defineProperty(e, t, { value: r, enumerable: !0, configurable: !0, writable: !0 }) : e[t] = r, e;
}
function L3(e) {
  var t = q3(e, "string");
  return sn(t) == "symbol" ? t : t + "";
}
function q3(e, t) {
  if (sn(e) != "object" || !e) return e;
  var r = e[Symbol.toPrimitive];
  if (r !== void 0) {
    var n = r.call(e, t);
    if (sn(n) != "object") return n;
    throw new TypeError("@@toPrimitive must return a primitive value.");
  }
  return (t === "string" ? String : Number)(e);
}
function en() {
  return en = Object.assign ? Object.assign.bind() : function(e) {
    for (var t = 1; t < arguments.length; t++) {
      var r = arguments[t];
      for (var n in r)
        Object.prototype.hasOwnProperty.call(r, n) && (e[n] = r[n]);
    }
    return e;
  }, en.apply(this, arguments);
}
function o1(e, t) {
  if (e == null) return {};
  var r = B3(e, t), n, a;
  if (Object.getOwnPropertySymbols) {
    var i = Object.getOwnPropertySymbols(e);
    for (a = 0; a < i.length; a++)
      n = i[a], !(t.indexOf(n) >= 0) && Object.prototype.propertyIsEnumerable.call(e, n) && (r[n] = e[n]);
  }
  return r;
}
function B3(e, t) {
  if (e == null) return {};
  var r = {};
  for (var n in e)
    if (Object.prototype.hasOwnProperty.call(e, n)) {
      if (t.indexOf(n) >= 0) continue;
      r[n] = e[n];
    }
  return r;
}
var F3 = function(t) {
  var r = t.fill;
  if (!r || r === "none")
    return null;
  var n = t.fillOpacity, a = t.x, i = t.y, o = t.width, u = t.height, l = t.ry;
  return /* @__PURE__ */ M.createElement("rect", {
    x: a,
    y: i,
    ry: l,
    width: o,
    height: u,
    stroke: "none",
    fill: r,
    fillOpacity: n,
    className: "recharts-cartesian-grid-bg"
  });
};
function Q_(e, t) {
  var r;
  if (/* @__PURE__ */ M.isValidElement(e))
    r = /* @__PURE__ */ M.cloneElement(e, t);
  else if (fe(e))
    r = e(t);
  else {
    var n = t.x1, a = t.y1, i = t.x2, o = t.y2, u = t.key, l = o1(t, k3), s = pe(l, !1);
    s.offset;
    var f = o1(s, I3);
    r = /* @__PURE__ */ M.createElement("line", en({}, f, {
      x1: n,
      y1: a,
      x2: i,
      y2: o,
      fill: "none",
      key: u
    }));
  }
  return r;
}
function z3(e) {
  var t = e.x, r = e.width, n = e.horizontal, a = n === void 0 ? !0 : n, i = e.horizontalPoints;
  if (!a || !i || !i.length)
    return null;
  var o = i.map(function(u, l) {
    var s = ut(ut({}, e), {}, {
      x1: t,
      y1: u,
      x2: t + r,
      y2: u,
      key: "line-".concat(l),
      index: l
    });
    return Q_(a, s);
  });
  return /* @__PURE__ */ M.createElement("g", {
    className: "recharts-cartesian-grid-horizontal"
  }, o);
}
function U3(e) {
  var t = e.y, r = e.height, n = e.vertical, a = n === void 0 ? !0 : n, i = e.verticalPoints;
  if (!a || !i || !i.length)
    return null;
  var o = i.map(function(u, l) {
    var s = ut(ut({}, e), {}, {
      x1: u,
      y1: t,
      x2: u,
      y2: t + r,
      key: "line-".concat(l),
      index: l
    });
    return Q_(a, s);
  });
  return /* @__PURE__ */ M.createElement("g", {
    className: "recharts-cartesian-grid-vertical"
  }, o);
}
function W3(e) {
  var t = e.horizontalFill, r = e.fillOpacity, n = e.x, a = e.y, i = e.width, o = e.height, u = e.horizontalPoints, l = e.horizontal, s = l === void 0 ? !0 : l;
  if (!s || !t || !t.length)
    return null;
  var f = u.map(function(d) {
    return Math.round(d + a - a);
  }).sort(function(d, h) {
    return d - h;
  });
  a !== f[0] && f.unshift(0);
  var c = f.map(function(d, h) {
    var y = !f[h + 1], v = y ? a + o - d : f[h + 1] - d;
    if (v <= 0)
      return null;
    var p = h % t.length;
    return /* @__PURE__ */ M.createElement("rect", {
      key: "react-".concat(h),
      y: d,
      x: n,
      height: v,
      width: i,
      stroke: "none",
      fill: t[p],
      fillOpacity: r,
      className: "recharts-cartesian-grid-bg"
    });
  });
  return /* @__PURE__ */ M.createElement("g", {
    className: "recharts-cartesian-gridstripes-horizontal"
  }, c);
}
function H3(e) {
  var t = e.vertical, r = t === void 0 ? !0 : t, n = e.verticalFill, a = e.fillOpacity, i = e.x, o = e.y, u = e.width, l = e.height, s = e.verticalPoints;
  if (!r || !n || !n.length)
    return null;
  var f = s.map(function(d) {
    return Math.round(d + i - i);
  }).sort(function(d, h) {
    return d - h;
  });
  i !== f[0] && f.unshift(0);
  var c = f.map(function(d, h) {
    var y = !f[h + 1], v = y ? i + u - d : f[h + 1] - d;
    if (v <= 0)
      return null;
    var p = h % n.length;
    return /* @__PURE__ */ M.createElement("rect", {
      key: "react-".concat(h),
      x: d,
      y: o,
      width: v,
      height: l,
      stroke: "none",
      fill: n[p],
      fillOpacity: a,
      className: "recharts-cartesian-grid-bg"
    });
  });
  return /* @__PURE__ */ M.createElement("g", {
    className: "recharts-cartesian-gridstripes-vertical"
  }, c);
}
var G3 = function(t, r) {
  var n = t.xAxis, a = t.width, i = t.height, o = t.offset;
  return d_(Wp(ut(ut(ut({}, fa.defaultProps), n), {}, {
    ticks: cr(n, !0),
    viewBox: {
      x: 0,
      y: 0,
      width: a,
      height: i
    }
  })), o.left, o.left + o.width, r);
}, K3 = function(t, r) {
  var n = t.yAxis, a = t.width, i = t.height, o = t.offset;
  return d_(Wp(ut(ut(ut({}, fa.defaultProps), n), {}, {
    ticks: cr(n, !0),
    viewBox: {
      x: 0,
      y: 0,
      width: a,
      height: i
    }
  })), o.top, o.top + o.height, r);
}, _n = {
  horizontal: !0,
  vertical: !0,
  stroke: "#ccc",
  fill: "none",
  // The fill of colors of grid lines
  verticalFill: [],
  horizontalFill: []
};
function lu(e) {
  var t, r, n, a, i, o, u = Bp(), l = Fp(), s = RB(), f = ut(ut({}, e), {}, {
    stroke: (t = e.stroke) !== null && t !== void 0 ? t : _n.stroke,
    fill: (r = e.fill) !== null && r !== void 0 ? r : _n.fill,
    horizontal: (n = e.horizontal) !== null && n !== void 0 ? n : _n.horizontal,
    horizontalFill: (a = e.horizontalFill) !== null && a !== void 0 ? a : _n.horizontalFill,
    vertical: (i = e.vertical) !== null && i !== void 0 ? i : _n.vertical,
    verticalFill: (o = e.verticalFill) !== null && o !== void 0 ? o : _n.verticalFill,
    x: H(e.x) ? e.x : s.left,
    y: H(e.y) ? e.y : s.top,
    width: H(e.width) ? e.width : s.width,
    height: H(e.height) ? e.height : s.height
  }), c = f.x, d = f.y, h = f.width, y = f.height, v = f.syncWithTicks, p = f.horizontalValues, g = f.verticalValues, b = NB(), w = CB();
  if (!H(h) || h <= 0 || !H(y) || y <= 0 || !H(c) || c !== +c || !H(d) || d !== +d)
    return null;
  var _ = f.verticalCoordinatesGenerator || G3, m = f.horizontalCoordinatesGenerator || K3, O = f.horizontalPoints, x = f.verticalPoints;
  if ((!O || !O.length) && fe(m)) {
    var S = p && p.length, T = m({
      yAxis: w ? ut(ut({}, w), {}, {
        ticks: S ? p : w.ticks
      }) : void 0,
      width: u,
      height: l,
      offset: s
    }, S ? !0 : v);
    dr(Array.isArray(T), "horizontalCoordinatesGenerator should return Array but instead it returned [".concat(sn(T), "]")), Array.isArray(T) && (O = T);
  }
  if ((!x || !x.length) && fe(_)) {
    var C = g && g.length, A = _({
      xAxis: b ? ut(ut({}, b), {}, {
        ticks: C ? g : b.ticks
      }) : void 0,
      width: u,
      height: l,
      offset: s
    }, C ? !0 : v);
    dr(Array.isArray(A), "verticalCoordinatesGenerator should return Array but instead it returned [".concat(sn(A), "]")), Array.isArray(A) && (x = A);
  }
  return /* @__PURE__ */ M.createElement("g", {
    className: "recharts-cartesian-grid"
  }, /* @__PURE__ */ M.createElement(F3, {
    fill: f.fill,
    fillOpacity: f.fillOpacity,
    x: f.x,
    y: f.y,
    width: f.width,
    height: f.height,
    ry: f.ry
  }), /* @__PURE__ */ M.createElement(z3, en({}, f, {
    offset: s,
    horizontalPoints: O,
    xAxis: b,
    yAxis: w
  })), /* @__PURE__ */ M.createElement(U3, en({}, f, {
    offset: s,
    verticalPoints: x,
    xAxis: b,
    yAxis: w
  })), /* @__PURE__ */ M.createElement(W3, en({}, f, {
    horizontalPoints: O
  })), /* @__PURE__ */ M.createElement(H3, en({}, f, {
    verticalPoints: x
  })));
}
lu.displayName = "CartesianGrid";
var V3 = ["layout", "type", "stroke", "connectNulls", "isRange", "ref"], X3 = ["key"], eS;
function Jn(e) {
  "@babel/helpers - typeof";
  return Jn = typeof Symbol == "function" && typeof Symbol.iterator == "symbol" ? function(t) {
    return typeof t;
  } : function(t) {
    return t && typeof Symbol == "function" && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t;
  }, Jn(e);
}
function tS(e, t) {
  if (e == null) return {};
  var r = Y3(e, t), n, a;
  if (Object.getOwnPropertySymbols) {
    var i = Object.getOwnPropertySymbols(e);
    for (a = 0; a < i.length; a++)
      n = i[a], !(t.indexOf(n) >= 0) && Object.prototype.propertyIsEnumerable.call(e, n) && (r[n] = e[n]);
  }
  return r;
}
function Y3(e, t) {
  if (e == null) return {};
  var r = {};
  for (var n in e)
    if (Object.prototype.hasOwnProperty.call(e, n)) {
      if (t.indexOf(n) >= 0) continue;
      r[n] = e[n];
    }
  return r;
}
function tn() {
  return tn = Object.assign ? Object.assign.bind() : function(e) {
    for (var t = 1; t < arguments.length; t++) {
      var r = arguments[t];
      for (var n in r)
        Object.prototype.hasOwnProperty.call(r, n) && (e[n] = r[n]);
    }
    return e;
  }, tn.apply(this, arguments);
}
function u1(e, t) {
  var r = Object.keys(e);
  if (Object.getOwnPropertySymbols) {
    var n = Object.getOwnPropertySymbols(e);
    t && (n = n.filter(function(a) {
      return Object.getOwnPropertyDescriptor(e, a).enumerable;
    })), r.push.apply(r, n);
  }
  return r;
}
function Pr(e) {
  for (var t = 1; t < arguments.length; t++) {
    var r = arguments[t] != null ? arguments[t] : {};
    t % 2 ? u1(Object(r), !0).forEach(function(n) {
      Gt(e, n, r[n]);
    }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(r)) : u1(Object(r)).forEach(function(n) {
      Object.defineProperty(e, n, Object.getOwnPropertyDescriptor(r, n));
    });
  }
  return e;
}
function Z3(e, t) {
  if (!(e instanceof t))
    throw new TypeError("Cannot call a class as a function");
}
function l1(e, t) {
  for (var r = 0; r < t.length; r++) {
    var n = t[r];
    n.enumerable = n.enumerable || !1, n.configurable = !0, "value" in n && (n.writable = !0), Object.defineProperty(e, nS(n.key), n);
  }
}
function J3(e, t, r) {
  return t && l1(e.prototype, t), r && l1(e, r), Object.defineProperty(e, "prototype", { writable: !1 }), e;
}
function Q3(e, t, r) {
  return t = su(t), e4(e, rS() ? Reflect.construct(t, r || [], su(e).constructor) : t.apply(e, r));
}
function e4(e, t) {
  if (t && (Jn(t) === "object" || typeof t == "function"))
    return t;
  if (t !== void 0)
    throw new TypeError("Derived constructors may only return object or undefined");
  return t4(e);
}
function t4(e) {
  if (e === void 0)
    throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
  return e;
}
function rS() {
  try {
    var e = !Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function() {
    }));
  } catch {
  }
  return (rS = function() {
    return !!e;
  })();
}
function su(e) {
  return su = Object.setPrototypeOf ? Object.getPrototypeOf.bind() : function(r) {
    return r.__proto__ || Object.getPrototypeOf(r);
  }, su(e);
}
function r4(e, t) {
  if (typeof t != "function" && t !== null)
    throw new TypeError("Super expression must either be null or a function");
  e.prototype = Object.create(t && t.prototype, { constructor: { value: e, writable: !0, configurable: !0 } }), Object.defineProperty(e, "prototype", { writable: !1 }), t && yh(e, t);
}
function yh(e, t) {
  return yh = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function(n, a) {
    return n.__proto__ = a, n;
  }, yh(e, t);
}
function Gt(e, t, r) {
  return t = nS(t), t in e ? Object.defineProperty(e, t, { value: r, enumerable: !0, configurable: !0, writable: !0 }) : e[t] = r, e;
}
function nS(e) {
  var t = n4(e, "string");
  return Jn(t) == "symbol" ? t : t + "";
}
function n4(e, t) {
  if (Jn(e) != "object" || !e) return e;
  var r = e[Symbol.toPrimitive];
  if (r !== void 0) {
    var n = r.call(e, t);
    if (Jn(n) != "object") return n;
    throw new TypeError("@@toPrimitive must return a primitive value.");
  }
  return String(e);
}
var Ur = /* @__PURE__ */ (function(e) {
  function t() {
    var r;
    Z3(this, t);
    for (var n = arguments.length, a = new Array(n), i = 0; i < n; i++)
      a[i] = arguments[i];
    return r = Q3(this, t, [].concat(a)), Gt(r, "state", {
      isAnimationFinished: !0
    }), Gt(r, "id", $i("recharts-area-")), Gt(r, "handleAnimationEnd", function() {
      var o = r.props.onAnimationEnd;
      r.setState({
        isAnimationFinished: !0
      }), fe(o) && o();
    }), Gt(r, "handleAnimationStart", function() {
      var o = r.props.onAnimationStart;
      r.setState({
        isAnimationFinished: !1
      }), fe(o) && o();
    }), r;
  }
  return r4(t, e), J3(t, [{
    key: "renderDots",
    value: function(n, a, i) {
      var o = this.props.isAnimationActive, u = this.state.isAnimationFinished;
      if (o && !u)
        return null;
      var l = this.props, s = l.dot, f = l.points, c = l.dataKey, d = pe(this.props, !1), h = pe(s, !0), y = f.map(function(p, g) {
        var b = Pr(Pr(Pr({
          key: "dot-".concat(g),
          r: 3
        }, d), h), {}, {
          index: g,
          cx: p.x,
          cy: p.y,
          dataKey: c,
          value: p.value,
          payload: p.payload,
          points: f
        });
        return t.renderDotItem(s, b);
      }), v = {
        clipPath: n ? "url(#clipPath-".concat(a ? "" : "dots-").concat(i, ")") : null
      };
      return /* @__PURE__ */ M.createElement(Ie, tn({
        className: "recharts-area-dots"
      }, v), y);
    }
  }, {
    key: "renderHorizontalRect",
    value: function(n) {
      var a = this.props, i = a.baseLine, o = a.points, u = a.strokeWidth, l = o[0].x, s = o[o.length - 1].x, f = n * Math.abs(l - s), c = Nr(o.map(function(d) {
        return d.y || 0;
      }));
      return H(i) && typeof i == "number" ? c = Math.max(i, c) : i && Array.isArray(i) && i.length && (c = Math.max(Nr(i.map(function(d) {
        return d.y || 0;
      })), c)), H(c) ? /* @__PURE__ */ M.createElement("rect", {
        x: l < s ? l : l - f,
        y: 0,
        width: f,
        height: Math.floor(c + (u ? parseInt("".concat(u), 10) : 1))
      }) : null;
    }
  }, {
    key: "renderVerticalRect",
    value: function(n) {
      var a = this.props, i = a.baseLine, o = a.points, u = a.strokeWidth, l = o[0].y, s = o[o.length - 1].y, f = n * Math.abs(l - s), c = Nr(o.map(function(d) {
        return d.x || 0;
      }));
      return H(i) && typeof i == "number" ? c = Math.max(i, c) : i && Array.isArray(i) && i.length && (c = Math.max(Nr(i.map(function(d) {
        return d.x || 0;
      })), c)), H(c) ? /* @__PURE__ */ M.createElement("rect", {
        x: 0,
        y: l < s ? l : l - f,
        width: c + (u ? parseInt("".concat(u), 10) : 1),
        height: Math.floor(f)
      }) : null;
    }
  }, {
    key: "renderClipRect",
    value: function(n) {
      var a = this.props.layout;
      return a === "vertical" ? this.renderVerticalRect(n) : this.renderHorizontalRect(n);
    }
  }, {
    key: "renderAreaStatically",
    value: function(n, a, i, o) {
      var u = this.props, l = u.layout, s = u.type, f = u.stroke, c = u.connectNulls, d = u.isRange;
      u.ref;
      var h = tS(u, V3);
      return /* @__PURE__ */ M.createElement(Ie, {
        clipPath: i ? "url(#clipPath-".concat(o, ")") : null
      }, /* @__PURE__ */ M.createElement(La, tn({}, pe(h, !0), {
        points: n,
        connectNulls: c,
        type: s,
        baseLine: a,
        layout: l,
        stroke: "none",
        className: "recharts-area-area"
      })), f !== "none" && /* @__PURE__ */ M.createElement(La, tn({}, pe(this.props, !1), {
        className: "recharts-area-curve",
        layout: l,
        type: s,
        connectNulls: c,
        fill: "none",
        points: n
      })), f !== "none" && d && /* @__PURE__ */ M.createElement(La, tn({}, pe(this.props, !1), {
        className: "recharts-area-curve",
        layout: l,
        type: s,
        connectNulls: c,
        fill: "none",
        points: a
      })));
    }
  }, {
    key: "renderAreaWithAnimation",
    value: function(n, a) {
      var i = this, o = this.props, u = o.points, l = o.baseLine, s = o.isAnimationActive, f = o.animationBegin, c = o.animationDuration, d = o.animationEasing, h = o.animationId, y = this.state, v = y.prevPoints, p = y.prevBaseLine;
      return /* @__PURE__ */ M.createElement(gr, {
        begin: f,
        duration: c,
        isActive: s,
        easing: d,
        from: {
          t: 0
        },
        to: {
          t: 1
        },
        key: "area-".concat(h),
        onAnimationEnd: this.handleAnimationEnd,
        onAnimationStart: this.handleAnimationStart
      }, function(g) {
        var b = g.t;
        if (v) {
          var w = v.length / u.length, _ = u.map(function(S, T) {
            var C = Math.floor(T * w);
            if (v[C]) {
              var A = v[C], N = At(A.x, S.x), $ = At(A.y, S.y);
              return Pr(Pr({}, S), {}, {
                x: N(b),
                y: $(b)
              });
            }
            return S;
          }), m;
          if (H(l) && typeof l == "number") {
            var O = At(p, l);
            m = O(b);
          } else if (me(l) || ia(l)) {
            var x = At(p, 0);
            m = x(b);
          } else
            m = l.map(function(S, T) {
              var C = Math.floor(T * w);
              if (p[C]) {
                var A = p[C], N = At(A.x, S.x), $ = At(A.y, S.y);
                return Pr(Pr({}, S), {}, {
                  x: N(b),
                  y: $(b)
                });
              }
              return S;
            });
          return i.renderAreaStatically(_, m, n, a);
        }
        return /* @__PURE__ */ M.createElement(Ie, null, /* @__PURE__ */ M.createElement("defs", null, /* @__PURE__ */ M.createElement("clipPath", {
          id: "animationClipPath-".concat(a)
        }, i.renderClipRect(b))), /* @__PURE__ */ M.createElement(Ie, {
          clipPath: "url(#animationClipPath-".concat(a, ")")
        }, i.renderAreaStatically(u, l, n, a)));
      });
    }
  }, {
    key: "renderArea",
    value: function(n, a) {
      var i = this.props, o = i.points, u = i.baseLine, l = i.isAnimationActive, s = this.state, f = s.prevPoints, c = s.prevBaseLine, d = s.totalLength;
      return l && o && o.length && (!f && d > 0 || !ri(f, o) || !ri(c, u)) ? this.renderAreaWithAnimation(n, a) : this.renderAreaStatically(o, u, n, a);
    }
  }, {
    key: "render",
    value: function() {
      var n, a = this.props, i = a.hide, o = a.dot, u = a.points, l = a.className, s = a.top, f = a.left, c = a.xAxis, d = a.yAxis, h = a.width, y = a.height, v = a.isAnimationActive, p = a.id;
      if (i || !u || !u.length)
        return null;
      var g = this.state.isAnimationFinished, b = u.length === 1, w = _e("recharts-area", l), _ = c && c.allowDataOverflow, m = d && d.allowDataOverflow, O = _ || m, x = me(p) ? this.id : p, S = (n = pe(o, !1)) !== null && n !== void 0 ? n : {
        r: 3,
        strokeWidth: 2
      }, T = S.r, C = T === void 0 ? 3 : T, A = S.strokeWidth, N = A === void 0 ? 2 : A, $ = PT(o) ? o : {}, D = $.clipDot, R = D === void 0 ? !0 : D, L = C * 2 + N;
      return /* @__PURE__ */ M.createElement(Ie, {
        className: w
      }, _ || m ? /* @__PURE__ */ M.createElement("defs", null, /* @__PURE__ */ M.createElement("clipPath", {
        id: "clipPath-".concat(x)
      }, /* @__PURE__ */ M.createElement("rect", {
        x: _ ? f : f - h / 2,
        y: m ? s : s - y / 2,
        width: _ ? h : h * 2,
        height: m ? y : y * 2
      })), !R && /* @__PURE__ */ M.createElement("clipPath", {
        id: "clipPath-dots-".concat(x)
      }, /* @__PURE__ */ M.createElement("rect", {
        x: f - L / 2,
        y: s - L / 2,
        width: h + L,
        height: y + L
      }))) : null, b ? null : this.renderArea(O, x), (o || b) && this.renderDots(O, R, x), (!v || g) && kr.renderCallByParent(this.props, u));
    }
  }], [{
    key: "getDerivedStateFromProps",
    value: function(n, a) {
      return n.animationId !== a.prevAnimationId ? {
        prevAnimationId: n.animationId,
        curPoints: n.points,
        curBaseLine: n.baseLine,
        prevPoints: a.curPoints,
        prevBaseLine: a.curBaseLine
      } : n.points !== a.curPoints || n.baseLine !== a.curBaseLine ? {
        curPoints: n.points,
        curBaseLine: n.baseLine
      } : null;
    }
  }]);
})(br);
eS = Ur;
Gt(Ur, "displayName", "Area");
Gt(Ur, "defaultProps", {
  stroke: "#3182bd",
  fill: "#3182bd",
  fillOpacity: 0.6,
  xAxisId: 0,
  yAxisId: 0,
  legendType: "line",
  connectNulls: !1,
  // points of area
  points: [],
  dot: !1,
  activeDot: !0,
  hide: !1,
  isAnimationActive: !ua.isSsr,
  animationBegin: 0,
  animationDuration: 1500,
  animationEasing: "ease"
});
Gt(Ur, "getBaseValue", function(e, t, r, n) {
  var a = e.layout, i = e.baseValue, o = t.props.baseValue, u = o ?? i;
  if (H(u) && typeof u == "number")
    return u;
  var l = a === "horizontal" ? n : r, s = l.scale.domain();
  if (l.type === "number") {
    var f = Math.max(s[0], s[1]), c = Math.min(s[0], s[1]);
    return u === "dataMin" ? c : u === "dataMax" || f < 0 ? f : Math.max(Math.min(s[0], s[1]), 0);
  }
  return u === "dataMin" ? s[0] : u === "dataMax" ? s[1] : s[0];
});
Gt(Ur, "getComposedData", function(e) {
  var t = e.props, r = e.item, n = e.xAxis, a = e.yAxis, i = e.xAxisTicks, o = e.yAxisTicks, u = e.bandSize, l = e.dataKey, s = e.stackedData, f = e.dataStartIndex, c = e.displayedData, d = e.offset, h = t.layout, y = s && s.length, v = eS.getBaseValue(t, r, n, a), p = h === "horizontal", g = !1, b = c.map(function(_, m) {
    var O;
    y ? O = s[f + m] : (O = gt(_, l), Array.isArray(O) ? g = !0 : O = [v, O]);
    var x = O[1] == null || y && gt(_, l) == null;
    return p ? {
      x: _b({
        axis: n,
        ticks: i,
        bandSize: u,
        entry: _,
        index: m
      }),
      y: x ? null : a.scale(O[1]),
      value: O,
      payload: _
    } : {
      x: x ? null : n.scale(O[1]),
      y: _b({
        axis: a,
        ticks: o,
        bandSize: u,
        entry: _,
        index: m
      }),
      value: O,
      payload: _
    };
  }), w;
  return y || g ? w = b.map(function(_) {
    var m = Array.isArray(_.value) ? _.value[0] : null;
    return p ? {
      x: _.x,
      y: m != null && _.y != null ? a.scale(m) : null
    } : {
      x: m != null ? n.scale(m) : null,
      y: _.y
    };
  }) : w = p ? a.scale(v) : n.scale(v), Pr({
    points: b,
    baseLine: w,
    layout: h,
    isRange: g
  }, d);
});
Gt(Ur, "renderDotItem", function(e, t) {
  var r;
  if (/* @__PURE__ */ M.isValidElement(e))
    r = /* @__PURE__ */ M.cloneElement(e, t);
  else if (fe(e))
    r = e(t);
  else {
    var n = _e("recharts-area-dot", typeof e != "boolean" ? e.className : ""), a = t.key, i = tS(t, X3);
    r = /* @__PURE__ */ M.createElement(Ip, tn({}, i, {
      key: a,
      className: n
    }));
  }
  return r;
});
function Qn(e) {
  "@babel/helpers - typeof";
  return Qn = typeof Symbol == "function" && typeof Symbol.iterator == "symbol" ? function(t) {
    return typeof t;
  } : function(t) {
    return t && typeof Symbol == "function" && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t;
  }, Qn(e);
}
function a4(e, t) {
  if (!(e instanceof t))
    throw new TypeError("Cannot call a class as a function");
}
function i4(e, t) {
  for (var r = 0; r < t.length; r++) {
    var n = t[r];
    n.enumerable = n.enumerable || !1, n.configurable = !0, "value" in n && (n.writable = !0), Object.defineProperty(e, oS(n.key), n);
  }
}
function o4(e, t, r) {
  return t && i4(e.prototype, t), Object.defineProperty(e, "prototype", { writable: !1 }), e;
}
function u4(e, t, r) {
  return t = cu(t), l4(e, aS() ? Reflect.construct(t, r || [], cu(e).constructor) : t.apply(e, r));
}
function l4(e, t) {
  if (t && (Qn(t) === "object" || typeof t == "function"))
    return t;
  if (t !== void 0)
    throw new TypeError("Derived constructors may only return object or undefined");
  return s4(e);
}
function s4(e) {
  if (e === void 0)
    throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
  return e;
}
function aS() {
  try {
    var e = !Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function() {
    }));
  } catch {
  }
  return (aS = function() {
    return !!e;
  })();
}
function cu(e) {
  return cu = Object.setPrototypeOf ? Object.getPrototypeOf.bind() : function(r) {
    return r.__proto__ || Object.getPrototypeOf(r);
  }, cu(e);
}
function c4(e, t) {
  if (typeof t != "function" && t !== null)
    throw new TypeError("Super expression must either be null or a function");
  e.prototype = Object.create(t && t.prototype, { constructor: { value: e, writable: !0, configurable: !0 } }), Object.defineProperty(e, "prototype", { writable: !1 }), t && mh(e, t);
}
function mh(e, t) {
  return mh = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function(n, a) {
    return n.__proto__ = a, n;
  }, mh(e, t);
}
function iS(e, t, r) {
  return t = oS(t), t in e ? Object.defineProperty(e, t, { value: r, enumerable: !0, configurable: !0, writable: !0 }) : e[t] = r, e;
}
function oS(e) {
  var t = f4(e, "string");
  return Qn(t) == "symbol" ? t : t + "";
}
function f4(e, t) {
  if (Qn(e) != "object" || !e) return e;
  var r = e[Symbol.toPrimitive];
  if (r !== void 0) {
    var n = r.call(e, t);
    if (Qn(n) != "object") return n;
    throw new TypeError("@@toPrimitive must return a primitive value.");
  }
  return String(e);
}
function gh() {
  return gh = Object.assign ? Object.assign.bind() : function(e) {
    for (var t = 1; t < arguments.length; t++) {
      var r = arguments[t];
      for (var n in r)
        Object.prototype.hasOwnProperty.call(r, n) && (e[n] = r[n]);
    }
    return e;
  }, gh.apply(this, arguments);
}
function d4(e) {
  var t = e.xAxisId, r = Bp(), n = Fp(), a = z_(t);
  return a == null ? null : (
    // @ts-expect-error the axisOptions type is not exactly what CartesianAxis is expecting.
    /* @__PURE__ */ M.createElement(fa, gh({}, a, {
      className: _e("recharts-".concat(a.axisType, " ").concat(a.axisType), a.className),
      viewBox: {
        x: 0,
        y: 0,
        width: r,
        height: n
      },
      ticksGenerator: function(o) {
        return cr(o, !0);
      }
    }))
  );
}
var cn = /* @__PURE__ */ (function(e) {
  function t() {
    return a4(this, t), u4(this, t, arguments);
  }
  return c4(t, e), o4(t, [{
    key: "render",
    value: function() {
      return /* @__PURE__ */ M.createElement(d4, this.props);
    }
  }]);
})(M.Component);
iS(cn, "displayName", "XAxis");
iS(cn, "defaultProps", {
  allowDecimals: !0,
  hide: !1,
  orientation: "bottom",
  width: 0,
  height: 30,
  mirror: !1,
  xAxisId: 0,
  tickCount: 5,
  type: "category",
  padding: {
    left: 0,
    right: 0
  },
  allowDataOverflow: !1,
  scale: "auto",
  reversed: !1,
  allowDuplicatedCategory: !0
});
function ea(e) {
  "@babel/helpers - typeof";
  return ea = typeof Symbol == "function" && typeof Symbol.iterator == "symbol" ? function(t) {
    return typeof t;
  } : function(t) {
    return t && typeof Symbol == "function" && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t;
  }, ea(e);
}
function h4(e, t) {
  if (!(e instanceof t))
    throw new TypeError("Cannot call a class as a function");
}
function p4(e, t) {
  for (var r = 0; r < t.length; r++) {
    var n = t[r];
    n.enumerable = n.enumerable || !1, n.configurable = !0, "value" in n && (n.writable = !0), Object.defineProperty(e, sS(n.key), n);
  }
}
function v4(e, t, r) {
  return t && p4(e.prototype, t), Object.defineProperty(e, "prototype", { writable: !1 }), e;
}
function y4(e, t, r) {
  return t = fu(t), m4(e, uS() ? Reflect.construct(t, r || [], fu(e).constructor) : t.apply(e, r));
}
function m4(e, t) {
  if (t && (ea(t) === "object" || typeof t == "function"))
    return t;
  if (t !== void 0)
    throw new TypeError("Derived constructors may only return object or undefined");
  return g4(e);
}
function g4(e) {
  if (e === void 0)
    throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
  return e;
}
function uS() {
  try {
    var e = !Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function() {
    }));
  } catch {
  }
  return (uS = function() {
    return !!e;
  })();
}
function fu(e) {
  return fu = Object.setPrototypeOf ? Object.getPrototypeOf.bind() : function(r) {
    return r.__proto__ || Object.getPrototypeOf(r);
  }, fu(e);
}
function b4(e, t) {
  if (typeof t != "function" && t !== null)
    throw new TypeError("Super expression must either be null or a function");
  e.prototype = Object.create(t && t.prototype, { constructor: { value: e, writable: !0, configurable: !0 } }), Object.defineProperty(e, "prototype", { writable: !1 }), t && bh(e, t);
}
function bh(e, t) {
  return bh = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function(n, a) {
    return n.__proto__ = a, n;
  }, bh(e, t);
}
function lS(e, t, r) {
  return t = sS(t), t in e ? Object.defineProperty(e, t, { value: r, enumerable: !0, configurable: !0, writable: !0 }) : e[t] = r, e;
}
function sS(e) {
  var t = x4(e, "string");
  return ea(t) == "symbol" ? t : t + "";
}
function x4(e, t) {
  if (ea(e) != "object" || !e) return e;
  var r = e[Symbol.toPrimitive];
  if (r !== void 0) {
    var n = r.call(e, t);
    if (ea(n) != "object") return n;
    throw new TypeError("@@toPrimitive must return a primitive value.");
  }
  return String(e);
}
function xh() {
  return xh = Object.assign ? Object.assign.bind() : function(e) {
    for (var t = 1; t < arguments.length; t++) {
      var r = arguments[t];
      for (var n in r)
        Object.prototype.hasOwnProperty.call(r, n) && (e[n] = r[n]);
    }
    return e;
  }, xh.apply(this, arguments);
}
var w4 = function(t) {
  var r = t.yAxisId, n = Bp(), a = Fp(), i = U_(r);
  return i == null ? null : (
    // @ts-expect-error the axisOptions type is not exactly what CartesianAxis is expecting.
    /* @__PURE__ */ M.createElement(fa, xh({}, i, {
      className: _e("recharts-".concat(i.axisType, " ").concat(i.axisType), i.className),
      viewBox: {
        x: 0,
        y: 0,
        width: n,
        height: a
      },
      ticksGenerator: function(u) {
        return cr(u, !0);
      }
    }))
  );
}, fn = /* @__PURE__ */ (function(e) {
  function t() {
    return h4(this, t), y4(this, t, arguments);
  }
  return b4(t, e), v4(t, [{
    key: "render",
    value: function() {
      return /* @__PURE__ */ M.createElement(w4, this.props);
    }
  }]);
})(M.Component);
lS(fn, "displayName", "YAxis");
lS(fn, "defaultProps", {
  allowDuplicatedCategory: !0,
  allowDecimals: !0,
  hide: !1,
  orientation: "left",
  width: 60,
  height: 0,
  mirror: !1,
  yAxisId: 0,
  tickCount: 5,
  type: "number",
  padding: {
    top: 0,
    bottom: 0
  },
  allowDataOverflow: !1,
  scale: "auto",
  reversed: !1
});
function s1(e) {
  return P4(e) || S4(e) || _4(e) || O4();
}
function O4() {
  throw new TypeError(`Invalid attempt to spread non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`);
}
function _4(e, t) {
  if (e) {
    if (typeof e == "string") return wh(e, t);
    var r = Object.prototype.toString.call(e).slice(8, -1);
    if (r === "Object" && e.constructor && (r = e.constructor.name), r === "Map" || r === "Set") return Array.from(e);
    if (r === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r)) return wh(e, t);
  }
}
function S4(e) {
  if (typeof Symbol < "u" && e[Symbol.iterator] != null || e["@@iterator"] != null) return Array.from(e);
}
function P4(e) {
  if (Array.isArray(e)) return wh(e);
}
function wh(e, t) {
  (t == null || t > e.length) && (t = e.length);
  for (var r = 0, n = new Array(t); r < t; r++) n[r] = e[r];
  return n;
}
var Oh = function(t, r, n, a, i) {
  var o = qt(t, Up), u = qt(t, Uu), l = [].concat(s1(o), s1(u)), s = qt(t, Hu), f = "".concat(a, "Id"), c = a[0], d = r;
  if (l.length && (d = l.reduce(function(v, p) {
    if (p.props[f] === n && Xt(p.props, "extendDomain") && H(p.props[c])) {
      var g = p.props[c];
      return [Math.min(v[0], g), Math.max(v[1], g)];
    }
    return v;
  }, d)), s.length) {
    var h = "".concat(c, "1"), y = "".concat(c, "2");
    d = s.reduce(function(v, p) {
      if (p.props[f] === n && Xt(p.props, "extendDomain") && H(p.props[h]) && H(p.props[y])) {
        var g = p.props[h], b = p.props[y];
        return [Math.min(v[0], g, b), Math.max(v[1], g, b)];
      }
      return v;
    }, d);
  }
  return i && i.length && (d = i.reduce(function(v, p) {
    return H(p) ? [Math.min(v[0], p), Math.max(v[1], p)] : v;
  }, d)), d;
}, ed = { exports: {} }, c1;
function A4() {
  return c1 || (c1 = 1, (function(e) {
    var t = Object.prototype.hasOwnProperty, r = "~";
    function n() {
    }
    Object.create && (n.prototype = /* @__PURE__ */ Object.create(null), new n().__proto__ || (r = !1));
    function a(l, s, f) {
      this.fn = l, this.context = s, this.once = f || !1;
    }
    function i(l, s, f, c, d) {
      if (typeof f != "function")
        throw new TypeError("The listener must be a function");
      var h = new a(f, c || l, d), y = r ? r + s : s;
      return l._events[y] ? l._events[y].fn ? l._events[y] = [l._events[y], h] : l._events[y].push(h) : (l._events[y] = h, l._eventsCount++), l;
    }
    function o(l, s) {
      --l._eventsCount === 0 ? l._events = new n() : delete l._events[s];
    }
    function u() {
      this._events = new n(), this._eventsCount = 0;
    }
    u.prototype.eventNames = function() {
      var s = [], f, c;
      if (this._eventsCount === 0) return s;
      for (c in f = this._events)
        t.call(f, c) && s.push(r ? c.slice(1) : c);
      return Object.getOwnPropertySymbols ? s.concat(Object.getOwnPropertySymbols(f)) : s;
    }, u.prototype.listeners = function(s) {
      var f = r ? r + s : s, c = this._events[f];
      if (!c) return [];
      if (c.fn) return [c.fn];
      for (var d = 0, h = c.length, y = new Array(h); d < h; d++)
        y[d] = c[d].fn;
      return y;
    }, u.prototype.listenerCount = function(s) {
      var f = r ? r + s : s, c = this._events[f];
      return c ? c.fn ? 1 : c.length : 0;
    }, u.prototype.emit = function(s, f, c, d, h, y) {
      var v = r ? r + s : s;
      if (!this._events[v]) return !1;
      var p = this._events[v], g = arguments.length, b, w;
      if (p.fn) {
        switch (p.once && this.removeListener(s, p.fn, void 0, !0), g) {
          case 1:
            return p.fn.call(p.context), !0;
          case 2:
            return p.fn.call(p.context, f), !0;
          case 3:
            return p.fn.call(p.context, f, c), !0;
          case 4:
            return p.fn.call(p.context, f, c, d), !0;
          case 5:
            return p.fn.call(p.context, f, c, d, h), !0;
          case 6:
            return p.fn.call(p.context, f, c, d, h, y), !0;
        }
        for (w = 1, b = new Array(g - 1); w < g; w++)
          b[w - 1] = arguments[w];
        p.fn.apply(p.context, b);
      } else {
        var _ = p.length, m;
        for (w = 0; w < _; w++)
          switch (p[w].once && this.removeListener(s, p[w].fn, void 0, !0), g) {
            case 1:
              p[w].fn.call(p[w].context);
              break;
            case 2:
              p[w].fn.call(p[w].context, f);
              break;
            case 3:
              p[w].fn.call(p[w].context, f, c);
              break;
            case 4:
              p[w].fn.call(p[w].context, f, c, d);
              break;
            default:
              if (!b) for (m = 1, b = new Array(g - 1); m < g; m++)
                b[m - 1] = arguments[m];
              p[w].fn.apply(p[w].context, b);
          }
      }
      return !0;
    }, u.prototype.on = function(s, f, c) {
      return i(this, s, f, c, !1);
    }, u.prototype.once = function(s, f, c) {
      return i(this, s, f, c, !0);
    }, u.prototype.removeListener = function(s, f, c, d) {
      var h = r ? r + s : s;
      if (!this._events[h]) return this;
      if (!f)
        return o(this, h), this;
      var y = this._events[h];
      if (y.fn)
        y.fn === f && (!d || y.once) && (!c || y.context === c) && o(this, h);
      else {
        for (var v = 0, p = [], g = y.length; v < g; v++)
          (y[v].fn !== f || d && !y[v].once || c && y[v].context !== c) && p.push(y[v]);
        p.length ? this._events[h] = p.length === 1 ? p[0] : p : o(this, h);
      }
      return this;
    }, u.prototype.removeAllListeners = function(s) {
      var f;
      return s ? (f = r ? r + s : s, this._events[f] && o(this, f)) : (this._events = new n(), this._eventsCount = 0), this;
    }, u.prototype.off = u.prototype.removeListener, u.prototype.addListener = u.prototype.on, u.prefixed = r, u.EventEmitter = u, e.exports = u;
  })(ed)), ed.exports;
}
var E4 = A4();
const T4 = /* @__PURE__ */ $e(E4);
var td = new T4(), rd = "recharts.syncMouseEvents";
function Si(e) {
  "@babel/helpers - typeof";
  return Si = typeof Symbol == "function" && typeof Symbol.iterator == "symbol" ? function(t) {
    return typeof t;
  } : function(t) {
    return t && typeof Symbol == "function" && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t;
  }, Si(e);
}
function M4(e, t) {
  if (!(e instanceof t))
    throw new TypeError("Cannot call a class as a function");
}
function j4(e, t) {
  for (var r = 0; r < t.length; r++) {
    var n = t[r];
    n.enumerable = n.enumerable || !1, n.configurable = !0, "value" in n && (n.writable = !0), Object.defineProperty(e, cS(n.key), n);
  }
}
function N4(e, t, r) {
  return t && j4(e.prototype, t), Object.defineProperty(e, "prototype", { writable: !1 }), e;
}
function nd(e, t, r) {
  return t = cS(t), t in e ? Object.defineProperty(e, t, { value: r, enumerable: !0, configurable: !0, writable: !0 }) : e[t] = r, e;
}
function cS(e) {
  var t = C4(e, "string");
  return Si(t) == "symbol" ? t : t + "";
}
function C4(e, t) {
  if (Si(e) != "object" || !e) return e;
  var r = e[Symbol.toPrimitive];
  if (r !== void 0) {
    var n = r.call(e, t);
    if (Si(n) != "object") return n;
    throw new TypeError("@@toPrimitive must return a primitive value.");
  }
  return String(e);
}
var $4 = /* @__PURE__ */ (function() {
  function e() {
    M4(this, e), nd(this, "activeIndex", 0), nd(this, "coordinateList", []), nd(this, "layout", "horizontal");
  }
  return N4(e, [{
    key: "setDetails",
    value: function(r) {
      var n, a = r.coordinateList, i = a === void 0 ? null : a, o = r.container, u = o === void 0 ? null : o, l = r.layout, s = l === void 0 ? null : l, f = r.offset, c = f === void 0 ? null : f, d = r.mouseHandlerCallback, h = d === void 0 ? null : d;
      this.coordinateList = (n = i ?? this.coordinateList) !== null && n !== void 0 ? n : [], this.container = u ?? this.container, this.layout = s ?? this.layout, this.offset = c ?? this.offset, this.mouseHandlerCallback = h ?? this.mouseHandlerCallback, this.activeIndex = Math.min(Math.max(this.activeIndex, 0), this.coordinateList.length - 1);
    }
  }, {
    key: "focus",
    value: function() {
      this.spoofMouse();
    }
  }, {
    key: "keyboardEvent",
    value: function(r) {
      if (this.coordinateList.length !== 0)
        switch (r.key) {
          case "ArrowRight": {
            if (this.layout !== "horizontal")
              return;
            this.activeIndex = Math.min(this.activeIndex + 1, this.coordinateList.length - 1), this.spoofMouse();
            break;
          }
          case "ArrowLeft": {
            if (this.layout !== "horizontal")
              return;
            this.activeIndex = Math.max(this.activeIndex - 1, 0), this.spoofMouse();
            break;
          }
        }
    }
  }, {
    key: "setIndex",
    value: function(r) {
      this.activeIndex = r;
    }
  }, {
    key: "spoofMouse",
    value: function() {
      var r, n;
      if (this.layout === "horizontal" && this.coordinateList.length !== 0) {
        var a = this.container.getBoundingClientRect(), i = a.x, o = a.y, u = a.height, l = this.coordinateList[this.activeIndex].coordinate, s = ((r = window) === null || r === void 0 ? void 0 : r.scrollX) || 0, f = ((n = window) === null || n === void 0 ? void 0 : n.scrollY) || 0, c = i + l + s, d = o + this.offset.top + u / 2 + f;
        this.mouseHandlerCallback({
          pageX: c,
          pageY: d
        });
      }
    }
  }]);
})();
function R4(e, t, r) {
  if (r === "number" && t === !0 && Array.isArray(e)) {
    var n = e?.[0], a = e?.[1];
    if (n && a && H(n) && H(a))
      return !0;
  }
  return !1;
}
function k4(e, t, r, n) {
  var a = n / 2;
  return {
    stroke: "none",
    fill: "#ccc",
    x: e === "horizontal" ? t.x - a : r.left + 0.5,
    y: e === "horizontal" ? r.top + 0.5 : t.y - a,
    width: e === "horizontal" ? n : r.width - 1,
    height: e === "horizontal" ? r.height - 1 : n
  };
}
function fS(e) {
  var t = e.cx, r = e.cy, n = e.radius, a = e.startAngle, i = e.endAngle, o = tt(t, r, n, a), u = tt(t, r, n, i);
  return {
    points: [o, u],
    cx: t,
    cy: r,
    radius: n,
    startAngle: a,
    endAngle: i
  };
}
function I4(e, t, r) {
  var n, a, i, o;
  if (e === "horizontal")
    n = t.x, i = n, a = r.top, o = r.top + r.height;
  else if (e === "vertical")
    a = t.y, o = a, n = r.left, i = r.left + r.width;
  else if (t.cx != null && t.cy != null)
    if (e === "centric") {
      var u = t.cx, l = t.cy, s = t.innerRadius, f = t.outerRadius, c = t.angle, d = tt(u, l, s, c), h = tt(u, l, f, c);
      n = d.x, a = d.y, i = h.x, o = h.y;
    } else
      return fS(t);
  return [{
    x: n,
    y: a
  }, {
    x: i,
    y: o
  }];
}
function Pi(e) {
  "@babel/helpers - typeof";
  return Pi = typeof Symbol == "function" && typeof Symbol.iterator == "symbol" ? function(t) {
    return typeof t;
  } : function(t) {
    return t && typeof Symbol == "function" && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t;
  }, Pi(e);
}
function f1(e, t) {
  var r = Object.keys(e);
  if (Object.getOwnPropertySymbols) {
    var n = Object.getOwnPropertySymbols(e);
    t && (n = n.filter(function(a) {
      return Object.getOwnPropertyDescriptor(e, a).enumerable;
    })), r.push.apply(r, n);
  }
  return r;
}
function uo(e) {
  for (var t = 1; t < arguments.length; t++) {
    var r = arguments[t] != null ? arguments[t] : {};
    t % 2 ? f1(Object(r), !0).forEach(function(n) {
      D4(e, n, r[n]);
    }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(r)) : f1(Object(r)).forEach(function(n) {
      Object.defineProperty(e, n, Object.getOwnPropertyDescriptor(r, n));
    });
  }
  return e;
}
function D4(e, t, r) {
  return t = L4(t), t in e ? Object.defineProperty(e, t, { value: r, enumerable: !0, configurable: !0, writable: !0 }) : e[t] = r, e;
}
function L4(e) {
  var t = q4(e, "string");
  return Pi(t) == "symbol" ? t : t + "";
}
function q4(e, t) {
  if (Pi(e) != "object" || !e) return e;
  var r = e[Symbol.toPrimitive];
  if (r !== void 0) {
    var n = r.call(e, t);
    if (Pi(n) != "object") return n;
    throw new TypeError("@@toPrimitive must return a primitive value.");
  }
  return (t === "string" ? String : Number)(e);
}
function B4(e) {
  var t, r, n = e.element, a = e.tooltipEventType, i = e.isActive, o = e.activeCoordinate, u = e.activePayload, l = e.offset, s = e.activeTooltipIndex, f = e.tooltipAxisBandSize, c = e.layout, d = e.chartName, h = (t = n.props.cursor) !== null && t !== void 0 ? t : (r = n.type.defaultProps) === null || r === void 0 ? void 0 : r.cursor;
  if (!n || !h || !i || !o || d !== "ScatterChart" && a !== "axis")
    return null;
  var y, v = La;
  if (d === "ScatterChart")
    y = o, v = zq;
  else if (d === "BarChart")
    y = k4(c, o, l, f), v = kp;
  else if (c === "radial") {
    var p = fS(o), g = p.cx, b = p.cy, w = p.radius, _ = p.startAngle, m = p.endAngle;
    y = {
      cx: g,
      cy: b,
      startAngle: _,
      endAngle: m,
      innerRadius: w,
      outerRadius: w
    }, v = m_;
  } else
    y = {
      points: I4(c, o, l)
    }, v = La;
  var O = uo(uo(uo(uo({
    stroke: "#ccc",
    pointerEvents: "none"
  }, l), y), pe(h, !1)), {}, {
    payload: u,
    payloadIndex: s,
    className: _e("recharts-tooltip-cursor", h.className)
  });
  return /* @__PURE__ */ Lt(h) ? /* @__PURE__ */ Ue(h, O) : /* @__PURE__ */ ue(v, O);
}
var F4 = ["item"], z4 = ["children", "className", "width", "height", "style", "compact", "title", "desc"];
function ta(e) {
  "@babel/helpers - typeof";
  return ta = typeof Symbol == "function" && typeof Symbol.iterator == "symbol" ? function(t) {
    return typeof t;
  } : function(t) {
    return t && typeof Symbol == "function" && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t;
  }, ta(e);
}
function Mn() {
  return Mn = Object.assign ? Object.assign.bind() : function(e) {
    for (var t = 1; t < arguments.length; t++) {
      var r = arguments[t];
      for (var n in r)
        Object.prototype.hasOwnProperty.call(r, n) && (e[n] = r[n]);
    }
    return e;
  }, Mn.apply(this, arguments);
}
function d1(e, t) {
  return H4(e) || W4(e, t) || hS(e, t) || U4();
}
function U4() {
  throw new TypeError(`Invalid attempt to destructure non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`);
}
function W4(e, t) {
  var r = e == null ? null : typeof Symbol < "u" && e[Symbol.iterator] || e["@@iterator"];
  if (r != null) {
    var n, a, i, o, u = [], l = !0, s = !1;
    try {
      if (i = (r = r.call(e)).next, t !== 0) for (; !(l = (n = i.call(r)).done) && (u.push(n.value), u.length !== t); l = !0) ;
    } catch (f) {
      s = !0, a = f;
    } finally {
      try {
        if (!l && r.return != null && (o = r.return(), Object(o) !== o)) return;
      } finally {
        if (s) throw a;
      }
    }
    return u;
  }
}
function H4(e) {
  if (Array.isArray(e)) return e;
}
function h1(e, t) {
  if (e == null) return {};
  var r = G4(e, t), n, a;
  if (Object.getOwnPropertySymbols) {
    var i = Object.getOwnPropertySymbols(e);
    for (a = 0; a < i.length; a++)
      n = i[a], !(t.indexOf(n) >= 0) && Object.prototype.propertyIsEnumerable.call(e, n) && (r[n] = e[n]);
  }
  return r;
}
function G4(e, t) {
  if (e == null) return {};
  var r = {};
  for (var n in e)
    if (Object.prototype.hasOwnProperty.call(e, n)) {
      if (t.indexOf(n) >= 0) continue;
      r[n] = e[n];
    }
  return r;
}
function K4(e, t) {
  if (!(e instanceof t))
    throw new TypeError("Cannot call a class as a function");
}
function V4(e, t) {
  for (var r = 0; r < t.length; r++) {
    var n = t[r];
    n.enumerable = n.enumerable || !1, n.configurable = !0, "value" in n && (n.writable = !0), Object.defineProperty(e, pS(n.key), n);
  }
}
function X4(e, t, r) {
  return t && V4(e.prototype, t), Object.defineProperty(e, "prototype", { writable: !1 }), e;
}
function Y4(e, t, r) {
  return t = du(t), Z4(e, dS() ? Reflect.construct(t, r || [], du(e).constructor) : t.apply(e, r));
}
function Z4(e, t) {
  if (t && (ta(t) === "object" || typeof t == "function"))
    return t;
  if (t !== void 0)
    throw new TypeError("Derived constructors may only return object or undefined");
  return J4(e);
}
function J4(e) {
  if (e === void 0)
    throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
  return e;
}
function dS() {
  try {
    var e = !Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function() {
    }));
  } catch {
  }
  return (dS = function() {
    return !!e;
  })();
}
function du(e) {
  return du = Object.setPrototypeOf ? Object.getPrototypeOf.bind() : function(r) {
    return r.__proto__ || Object.getPrototypeOf(r);
  }, du(e);
}
function Q4(e, t) {
  if (typeof t != "function" && t !== null)
    throw new TypeError("Super expression must either be null or a function");
  e.prototype = Object.create(t && t.prototype, { constructor: { value: e, writable: !0, configurable: !0 } }), Object.defineProperty(e, "prototype", { writable: !1 }), t && _h(e, t);
}
function _h(e, t) {
  return _h = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function(n, a) {
    return n.__proto__ = a, n;
  }, _h(e, t);
}
function ra(e) {
  return rF(e) || tF(e) || hS(e) || eF();
}
function eF() {
  throw new TypeError(`Invalid attempt to spread non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`);
}
function hS(e, t) {
  if (e) {
    if (typeof e == "string") return Sh(e, t);
    var r = Object.prototype.toString.call(e).slice(8, -1);
    if (r === "Object" && e.constructor && (r = e.constructor.name), r === "Map" || r === "Set") return Array.from(e);
    if (r === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r)) return Sh(e, t);
  }
}
function tF(e) {
  if (typeof Symbol < "u" && e[Symbol.iterator] != null || e["@@iterator"] != null) return Array.from(e);
}
function rF(e) {
  if (Array.isArray(e)) return Sh(e);
}
function Sh(e, t) {
  (t == null || t > e.length) && (t = e.length);
  for (var r = 0, n = new Array(t); r < t; r++) n[r] = e[r];
  return n;
}
function p1(e, t) {
  var r = Object.keys(e);
  if (Object.getOwnPropertySymbols) {
    var n = Object.getOwnPropertySymbols(e);
    t && (n = n.filter(function(a) {
      return Object.getOwnPropertyDescriptor(e, a).enumerable;
    })), r.push.apply(r, n);
  }
  return r;
}
function q(e) {
  for (var t = 1; t < arguments.length; t++) {
    var r = arguments[t] != null ? arguments[t] : {};
    t % 2 ? p1(Object(r), !0).forEach(function(n) {
      ie(e, n, r[n]);
    }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(r)) : p1(Object(r)).forEach(function(n) {
      Object.defineProperty(e, n, Object.getOwnPropertyDescriptor(r, n));
    });
  }
  return e;
}
function ie(e, t, r) {
  return t = pS(t), t in e ? Object.defineProperty(e, t, { value: r, enumerable: !0, configurable: !0, writable: !0 }) : e[t] = r, e;
}
function pS(e) {
  var t = nF(e, "string");
  return ta(t) == "symbol" ? t : t + "";
}
function nF(e, t) {
  if (ta(e) != "object" || !e) return e;
  var r = e[Symbol.toPrimitive];
  if (r !== void 0) {
    var n = r.call(e, t);
    if (ta(n) != "object") return n;
    throw new TypeError("@@toPrimitive must return a primitive value.");
  }
  return (t === "string" ? String : Number)(e);
}
var aF = {
  xAxis: ["bottom", "top"],
  yAxis: ["left", "right"]
}, iF = {
  width: "100%",
  height: "100%"
}, vS = {
  x: 0,
  y: 0
};
function lo(e) {
  return e;
}
var oF = function(t, r) {
  return r === "horizontal" ? t.x : r === "vertical" ? t.y : r === "centric" ? t.angle : t.radius;
}, uF = function(t, r, n, a) {
  var i = r.find(function(f) {
    return f && f.index === n;
  });
  if (i) {
    if (t === "horizontal")
      return {
        x: i.coordinate,
        y: a.y
      };
    if (t === "vertical")
      return {
        x: a.x,
        y: i.coordinate
      };
    if (t === "centric") {
      var o = i.coordinate, u = a.radius;
      return q(q(q({}, a), tt(a.cx, a.cy, u, o)), {}, {
        angle: o,
        radius: u
      });
    }
    var l = i.coordinate, s = a.angle;
    return q(q(q({}, a), tt(a.cx, a.cy, l, s)), {}, {
      angle: s,
      radius: l
    });
  }
  return vS;
}, Gu = function(t, r) {
  var n = r.graphicalItems, a = r.dataStartIndex, i = r.dataEndIndex, o = (n ?? []).reduce(function(u, l) {
    var s = l.props.data;
    return s && s.length ? [].concat(ra(u), ra(s)) : u;
  }, []);
  return o.length > 0 ? o : t && t.length && H(a) && H(i) ? t.slice(a, i + 1) : [];
};
function yS(e) {
  return e === "number" ? [0, "auto"] : void 0;
}
var Ph = function(t, r, n, a) {
  var i = t.graphicalItems, o = t.tooltipAxis, u = Gu(r, t);
  return n < 0 || !i || !i.length || n >= u.length ? null : i.reduce(function(l, s) {
    var f, c = (f = s.props.data) !== null && f !== void 0 ? f : r;
    c && t.dataStartIndex + t.dataEndIndex !== 0 && // https://github.com/recharts/recharts/issues/4717
    // The data is sliced only when the active index is within the start/end index range.
    t.dataEndIndex - t.dataStartIndex >= n && (c = c.slice(t.dataStartIndex, t.dataEndIndex + 1));
    var d;
    if (o.dataKey && !o.allowDuplicatedCategory) {
      var h = c === void 0 ? u : c;
      d = yo(h, o.dataKey, a);
    } else
      d = c && c[n] || u[n];
    return d ? [].concat(ra(l), [p_(s, d)]) : l;
  }, []);
}, v1 = function(t, r, n, a) {
  var i = a || {
    x: t.chartX,
    y: t.chartY
  }, o = oF(i, n), u = t.orderedTooltipTicks, l = t.tooltipAxis, s = t.tooltipTicks, f = SI(o, u, s, l);
  if (f >= 0 && s) {
    var c = s[f] && s[f].value, d = Ph(t, r, f, c), h = uF(n, u, f, i);
    return {
      activeTooltipIndex: f,
      activeLabel: c,
      activePayload: d,
      activeCoordinate: h
    };
  }
  return null;
}, lF = function(t, r) {
  var n = r.axes, a = r.graphicalItems, i = r.axisType, o = r.axisIdKey, u = r.stackGroups, l = r.dataStartIndex, s = r.dataEndIndex, f = t.layout, c = t.children, d = t.stackOffset, h = f_(f, i);
  return n.reduce(function(y, v) {
    var p, g = v.type.defaultProps !== void 0 ? q(q({}, v.type.defaultProps), v.props) : v.props, b = g.type, w = g.dataKey, _ = g.allowDataOverflow, m = g.allowDuplicatedCategory, O = g.scale, x = g.ticks, S = g.includeHidden, T = g[o];
    if (y[T])
      return y;
    var C = Gu(t.data, {
      graphicalItems: a.filter(function(G) {
        var Q, de = o in G.props ? G.props[o] : (Q = G.type.defaultProps) === null || Q === void 0 ? void 0 : Q[o];
        return de === T;
      }),
      dataStartIndex: l,
      dataEndIndex: s
    }), A = C.length, N, $, D;
    R4(g.domain, _, b) && (N = Wd(g.domain, null, _), h && (b === "number" || O !== "auto") && (D = Da(C, w, "category")));
    var R = yS(b);
    if (!N || N.length === 0) {
      var L, z = (L = g.domain) !== null && L !== void 0 ? L : R;
      if (w) {
        if (N = Da(C, w, b), b === "category" && h) {
          var F = yT(N);
          m && F ? ($ = N, N = Jo(0, A)) : m || (N = Eb(z, N, v).reduce(function(G, Q) {
            return G.indexOf(Q) >= 0 ? G : [].concat(ra(G), [Q]);
          }, []));
        } else if (b === "category")
          m ? N = N.filter(function(G) {
            return G !== "" && !me(G);
          }) : N = Eb(z, N, v).reduce(function(G, Q) {
            return G.indexOf(Q) >= 0 || Q === "" || me(Q) ? G : [].concat(ra(G), [Q]);
          }, []);
        else if (b === "number") {
          var W = MI(C, a.filter(function(G) {
            var Q, de, ge = o in G.props ? G.props[o] : (Q = G.type.defaultProps) === null || Q === void 0 ? void 0 : Q[o], qe = "hide" in G.props ? G.props.hide : (de = G.type.defaultProps) === null || de === void 0 ? void 0 : de.hide;
            return ge === T && (S || !qe);
          }), w, i, f);
          W && (N = W);
        }
        h && (b === "number" || O !== "auto") && (D = Da(C, w, "category"));
      } else h ? N = Jo(0, A) : u && u[T] && u[T].hasStack && b === "number" ? N = d === "expand" ? [0, 1] : h_(u[T].stackGroups, l, s) : N = c_(C, a.filter(function(G) {
        var Q = o in G.props ? G.props[o] : G.type.defaultProps[o], de = "hide" in G.props ? G.props.hide : G.type.defaultProps.hide;
        return Q === T && (S || !de);
      }), b, f, !0);
      if (b === "number")
        N = Oh(c, N, T, i, x), z && (N = Wd(z, N, _));
      else if (b === "category" && z) {
        var X = z, J = N.every(function(G) {
          return X.indexOf(G) >= 0;
        });
        J && (N = X);
      }
    }
    return q(q({}, y), {}, ie({}, T, q(q({}, g), {}, {
      axisType: i,
      domain: N,
      categoricalDomain: D,
      duplicateDomain: $,
      originalDomain: (p = g.domain) !== null && p !== void 0 ? p : R,
      isCategorical: h,
      layout: f
    })));
  }, {});
}, sF = function(t, r) {
  var n = r.graphicalItems, a = r.Axis, i = r.axisType, o = r.axisIdKey, u = r.stackGroups, l = r.dataStartIndex, s = r.dataEndIndex, f = t.layout, c = t.children, d = Gu(t.data, {
    graphicalItems: n,
    dataStartIndex: l,
    dataEndIndex: s
  }), h = d.length, y = f_(f, i), v = -1;
  return n.reduce(function(p, g) {
    var b = g.type.defaultProps !== void 0 ? q(q({}, g.type.defaultProps), g.props) : g.props, w = b[o], _ = yS("number");
    if (!p[w]) {
      v++;
      var m;
      return y ? m = Jo(0, h) : u && u[w] && u[w].hasStack ? (m = h_(u[w].stackGroups, l, s), m = Oh(c, m, w, i)) : (m = Wd(_, c_(d, n.filter(function(O) {
        var x, S, T = o in O.props ? O.props[o] : (x = O.type.defaultProps) === null || x === void 0 ? void 0 : x[o], C = "hide" in O.props ? O.props.hide : (S = O.type.defaultProps) === null || S === void 0 ? void 0 : S.hide;
        return T === w && !C;
      }), "number", f), a.defaultProps.allowDataOverflow), m = Oh(c, m, w, i)), q(q({}, p), {}, ie({}, w, q(q({
        axisType: i
      }, a.defaultProps), {}, {
        hide: !0,
        orientation: Tt(aF, "".concat(i, ".").concat(v % 2), null),
        domain: m,
        originalDomain: _,
        isCategorical: y,
        layout: f
        // specify scale when no Axis
        // scale: isCategorical ? 'band' : 'linear',
      })));
    }
    return p;
  }, {});
}, cF = function(t, r) {
  var n = r.axisType, a = n === void 0 ? "xAxis" : n, i = r.AxisComp, o = r.graphicalItems, u = r.stackGroups, l = r.dataStartIndex, s = r.dataEndIndex, f = t.children, c = "".concat(a, "Id"), d = qt(f, i), h = {};
  return d && d.length ? h = lF(t, {
    axes: d,
    graphicalItems: o,
    axisType: a,
    axisIdKey: c,
    stackGroups: u,
    dataStartIndex: l,
    dataEndIndex: s
  }) : o && o.length && (h = sF(t, {
    Axis: i,
    graphicalItems: o,
    axisType: a,
    axisIdKey: c,
    stackGroups: u,
    dataStartIndex: l,
    dataEndIndex: s
  })), h;
}, fF = function(t) {
  var r = Mr(t), n = cr(r, !1, !0);
  return {
    tooltipTicks: n,
    orderedTooltipTicks: lp(n, function(a) {
      return a.coordinate;
    }),
    tooltipAxis: r,
    tooltipAxisBandSize: Uo(r, n)
  };
}, y1 = function(t) {
  var r = t.children, n = t.defaultShowTooltip, a = vt(r, Gn), i = 0, o = 0;
  return t.data && t.data.length !== 0 && (o = t.data.length - 1), a && a.props && (a.props.startIndex >= 0 && (i = a.props.startIndex), a.props.endIndex >= 0 && (o = a.props.endIndex)), {
    chartX: 0,
    chartY: 0,
    dataStartIndex: i,
    dataEndIndex: o,
    activeTooltipIndex: -1,
    isTooltipActive: !!n
  };
}, dF = function(t) {
  return !t || !t.length ? !1 : t.some(function(r) {
    var n = fr(r && r.type);
    return n && n.indexOf("Bar") >= 0;
  });
}, m1 = function(t) {
  return t === "horizontal" ? {
    numericAxisName: "yAxis",
    cateAxisName: "xAxis"
  } : t === "vertical" ? {
    numericAxisName: "xAxis",
    cateAxisName: "yAxis"
  } : t === "centric" ? {
    numericAxisName: "radiusAxis",
    cateAxisName: "angleAxis"
  } : {
    numericAxisName: "angleAxis",
    cateAxisName: "radiusAxis"
  };
}, hF = function(t, r) {
  var n = t.props, a = t.graphicalItems, i = t.xAxisMap, o = i === void 0 ? {} : i, u = t.yAxisMap, l = u === void 0 ? {} : u, s = n.width, f = n.height, c = n.children, d = n.margin || {}, h = vt(c, Gn), y = vt(c, rn), v = Object.keys(l).reduce(function(m, O) {
    var x = l[O], S = x.orientation;
    return !x.mirror && !x.hide ? q(q({}, m), {}, ie({}, S, m[S] + x.width)) : m;
  }, {
    left: d.left || 0,
    right: d.right || 0
  }), p = Object.keys(o).reduce(function(m, O) {
    var x = o[O], S = x.orientation;
    return !x.mirror && !x.hide ? q(q({}, m), {}, ie({}, S, Tt(m, "".concat(S)) + x.height)) : m;
  }, {
    top: d.top || 0,
    bottom: d.bottom || 0
  }), g = q(q({}, p), v), b = g.bottom;
  h && (g.bottom += h.props.height || Gn.defaultProps.height), y && r && (g = EI(g, a, n, r));
  var w = s - g.left - g.right, _ = f - g.top - g.bottom;
  return q(q({
    brushBottom: b
  }, g), {}, {
    // never return negative values for height and width
    width: Math.max(w, 0),
    height: Math.max(_, 0)
  });
}, pF = function(t, r) {
  if (r === "xAxis")
    return t[r].width;
  if (r === "yAxis")
    return t[r].height;
}, mS = function(t) {
  var r = t.chartName, n = t.GraphicalChild, a = t.defaultTooltipEventType, i = a === void 0 ? "axis" : a, o = t.validateTooltipEventTypes, u = o === void 0 ? ["axis"] : o, l = t.axisComponents, s = t.legendContent, f = t.formatAxisMap, c = t.defaultProps, d = function(g, b) {
    var w = b.graphicalItems, _ = b.stackGroups, m = b.offset, O = b.updateId, x = b.dataStartIndex, S = b.dataEndIndex, T = g.barSize, C = g.layout, A = g.barGap, N = g.barCategoryGap, $ = g.maxBarSize, D = m1(C), R = D.numericAxisName, L = D.cateAxisName, z = dF(w), F = [];
    return w.forEach(function(W, X) {
      var J = Gu(g.data, {
        graphicalItems: [W],
        dataStartIndex: x,
        dataEndIndex: S
      }), G = W.type.defaultProps !== void 0 ? q(q({}, W.type.defaultProps), W.props) : W.props, Q = G.dataKey, de = G.maxBarSize, ge = G["".concat(R, "Id")], qe = G["".concat(L, "Id")], bt = {}, Fe = l.reduce(function(tr, rr) {
        var da = b["".concat(rr.axisType, "Map")], Ut = G["".concat(rr.axisType, "Id")];
        da && da[Ut] || rr.axisType === "zAxis" || ln();
        var _r = da[Ut];
        return q(q({}, tr), {}, ie(ie({}, rr.axisType, _r), "".concat(rr.axisType, "Ticks"), cr(_r)));
      }, bt), V = Fe[L], le = Fe["".concat(L, "Ticks")], ce = _ && _[ge] && _[ge].hasStack && FI(W, _[ge].stackGroups), B = fr(W.type).indexOf("Bar") >= 0, Ee = Uo(V, le), ye = [], Be = z && PI({
        barSize: T,
        stackGroups: _,
        totalSize: pF(Fe, L)
      });
      if (B) {
        var je, rt, zt = me(de) ? $ : de, er = (je = (rt = Uo(V, le, !0)) !== null && rt !== void 0 ? rt : zt) !== null && je !== void 0 ? je : 0;
        ye = AI({
          barGap: A,
          barCategoryGap: N,
          bandSize: er !== Ee ? er : Ee,
          sizeList: Be[qe],
          maxBarSize: zt
        }), er !== Ee && (ye = ye.map(function(tr) {
          return q(q({}, tr), {}, {
            position: q(q({}, tr.position), {}, {
              offset: tr.position.offset - er / 2
            })
          });
        }));
      }
      var bn = W && W.type && W.type.getComposedData;
      bn && F.push({
        props: q(q({}, bn(q(q({}, Fe), {}, {
          displayedData: J,
          props: g,
          dataKey: Q,
          item: W,
          bandSize: Ee,
          barPosition: ye,
          offset: m,
          stackedData: ce,
          layout: C,
          dataStartIndex: x,
          dataEndIndex: S
        }))), {}, ie(ie(ie({
          key: W.key || "item-".concat(X)
        }, R, Fe[R]), L, Fe[L]), "animationId", O)),
        childIndex: TT(W, g.children),
        item: W
      });
    }), F;
  }, h = function(g, b) {
    var w = g.props, _ = g.dataStartIndex, m = g.dataEndIndex, O = g.updateId;
    if (!Fy({
      props: w
    }))
      return null;
    var x = w.children, S = w.layout, T = w.stackOffset, C = w.data, A = w.reverseStackOrder, N = m1(S), $ = N.numericAxisName, D = N.cateAxisName, R = qt(x, n), L = LI(C, R, "".concat($, "Id"), "".concat(D, "Id"), T, A), z = l.reduce(function(G, Q) {
      var de = "".concat(Q.axisType, "Map");
      return q(q({}, G), {}, ie({}, de, cF(w, q(q({}, Q), {}, {
        graphicalItems: R,
        stackGroups: Q.axisType === $ && L,
        dataStartIndex: _,
        dataEndIndex: m
      }))));
    }, {}), F = hF(q(q({}, z), {}, {
      props: w,
      graphicalItems: R
    }), b?.legendBBox);
    Object.keys(z).forEach(function(G) {
      z[G] = f(w, z[G], F, G.replace("Map", ""), r);
    });
    var W = z["".concat(D, "Map")], X = fF(W), J = d(w, q(q({}, z), {}, {
      dataStartIndex: _,
      dataEndIndex: m,
      updateId: O,
      graphicalItems: R,
      stackGroups: L,
      offset: F
    }));
    return q(q({
      formattedGraphicalItems: J,
      graphicalItems: R,
      offset: F,
      stackGroups: L
    }, X), z);
  }, y = /* @__PURE__ */ (function(p) {
    function g(b) {
      var w, _, m;
      return K4(this, g), m = Y4(this, g, [b]), ie(m, "eventEmitterSymbol", Symbol("rechartsEventEmitter")), ie(m, "accessibilityManager", new $4()), ie(m, "handleLegendBBoxUpdate", function(O) {
        if (O) {
          var x = m.state, S = x.dataStartIndex, T = x.dataEndIndex, C = x.updateId;
          m.setState(q({
            legendBBox: O
          }, h({
            props: m.props,
            dataStartIndex: S,
            dataEndIndex: T,
            updateId: C
          }, q(q({}, m.state), {}, {
            legendBBox: O
          }))));
        }
      }), ie(m, "handleReceiveSyncEvent", function(O, x, S) {
        if (m.props.syncId === O) {
          if (S === m.eventEmitterSymbol && typeof m.props.syncMethod != "function")
            return;
          m.applySyncEvent(x);
        }
      }), ie(m, "handleBrushChange", function(O) {
        var x = O.startIndex, S = O.endIndex;
        if (x !== m.state.dataStartIndex || S !== m.state.dataEndIndex) {
          var T = m.state.updateId;
          m.setState(function() {
            return q({
              dataStartIndex: x,
              dataEndIndex: S
            }, h({
              props: m.props,
              dataStartIndex: x,
              dataEndIndex: S,
              updateId: T
            }, m.state));
          }), m.triggerSyncEvent({
            dataStartIndex: x,
            dataEndIndex: S
          });
        }
      }), ie(m, "handleMouseEnter", function(O) {
        var x = m.getMouseInfo(O);
        if (x) {
          var S = q(q({}, x), {}, {
            isTooltipActive: !0
          });
          m.setState(S), m.triggerSyncEvent(S);
          var T = m.props.onMouseEnter;
          fe(T) && T(S, O);
        }
      }), ie(m, "triggeredAfterMouseMove", function(O) {
        var x = m.getMouseInfo(O), S = x ? q(q({}, x), {}, {
          isTooltipActive: !0
        }) : {
          isTooltipActive: !1
        };
        m.setState(S), m.triggerSyncEvent(S);
        var T = m.props.onMouseMove;
        fe(T) && T(S, O);
      }), ie(m, "handleItemMouseEnter", function(O) {
        m.setState(function() {
          return {
            isTooltipActive: !0,
            activeItem: O,
            activePayload: O.tooltipPayload,
            activeCoordinate: O.tooltipPosition || {
              x: O.cx,
              y: O.cy
            }
          };
        });
      }), ie(m, "handleItemMouseLeave", function() {
        m.setState(function() {
          return {
            isTooltipActive: !1
          };
        });
      }), ie(m, "handleMouseMove", function(O) {
        O.persist(), m.throttleTriggeredAfterMouseMove(O);
      }), ie(m, "handleMouseLeave", function(O) {
        m.throttleTriggeredAfterMouseMove.cancel();
        var x = {
          isTooltipActive: !1
        };
        m.setState(x), m.triggerSyncEvent(x);
        var S = m.props.onMouseLeave;
        fe(S) && S(x, O);
      }), ie(m, "handleOuterEvent", function(O) {
        var x = ET(O), S = Tt(m.props, "".concat(x));
        if (x && fe(S)) {
          var T, C;
          /.*touch.*/i.test(x) ? C = m.getMouseInfo(O.changedTouches[0]) : C = m.getMouseInfo(O), S((T = C) !== null && T !== void 0 ? T : {}, O);
        }
      }), ie(m, "handleClick", function(O) {
        var x = m.getMouseInfo(O);
        if (x) {
          var S = q(q({}, x), {}, {
            isTooltipActive: !0
          });
          m.setState(S), m.triggerSyncEvent(S);
          var T = m.props.onClick;
          fe(T) && T(S, O);
        }
      }), ie(m, "handleMouseDown", function(O) {
        var x = m.props.onMouseDown;
        if (fe(x)) {
          var S = m.getMouseInfo(O);
          x(S, O);
        }
      }), ie(m, "handleMouseUp", function(O) {
        var x = m.props.onMouseUp;
        if (fe(x)) {
          var S = m.getMouseInfo(O);
          x(S, O);
        }
      }), ie(m, "handleTouchMove", function(O) {
        O.changedTouches != null && O.changedTouches.length > 0 && m.throttleTriggeredAfterMouseMove(O.changedTouches[0]);
      }), ie(m, "handleTouchStart", function(O) {
        O.changedTouches != null && O.changedTouches.length > 0 && m.handleMouseDown(O.changedTouches[0]);
      }), ie(m, "handleTouchEnd", function(O) {
        O.changedTouches != null && O.changedTouches.length > 0 && m.handleMouseUp(O.changedTouches[0]);
      }), ie(m, "handleDoubleClick", function(O) {
        var x = m.props.onDoubleClick;
        if (fe(x)) {
          var S = m.getMouseInfo(O);
          x(S, O);
        }
      }), ie(m, "handleContextMenu", function(O) {
        var x = m.props.onContextMenu;
        if (fe(x)) {
          var S = m.getMouseInfo(O);
          x(S, O);
        }
      }), ie(m, "triggerSyncEvent", function(O) {
        m.props.syncId !== void 0 && td.emit(rd, m.props.syncId, O, m.eventEmitterSymbol);
      }), ie(m, "applySyncEvent", function(O) {
        var x = m.props, S = x.layout, T = x.syncMethod, C = m.state.updateId, A = O.dataStartIndex, N = O.dataEndIndex;
        if (O.dataStartIndex !== void 0 || O.dataEndIndex !== void 0)
          m.setState(q({
            dataStartIndex: A,
            dataEndIndex: N
          }, h({
            props: m.props,
            dataStartIndex: A,
            dataEndIndex: N,
            updateId: C
          }, m.state)));
        else if (O.activeTooltipIndex !== void 0) {
          var $ = O.chartX, D = O.chartY, R = O.activeTooltipIndex, L = m.state, z = L.offset, F = L.tooltipTicks;
          if (!z)
            return;
          if (typeof T == "function")
            R = T(F, O);
          else if (T === "value") {
            R = -1;
            for (var W = 0; W < F.length; W++)
              if (F[W].value === O.activeLabel) {
                R = W;
                break;
              }
          }
          var X = q(q({}, z), {}, {
            x: z.left,
            y: z.top
          }), J = Math.min($, X.x + X.width), G = Math.min(D, X.y + X.height), Q = F[R] && F[R].value, de = Ph(m.state, m.props.data, R), ge = F[R] ? {
            x: S === "horizontal" ? F[R].coordinate : J,
            y: S === "horizontal" ? G : F[R].coordinate
          } : vS;
          m.setState(q(q({}, O), {}, {
            activeLabel: Q,
            activeCoordinate: ge,
            activePayload: de,
            activeTooltipIndex: R
          }));
        } else
          m.setState(O);
      }), ie(m, "renderCursor", function(O) {
        var x, S = m.state, T = S.isTooltipActive, C = S.activeCoordinate, A = S.activePayload, N = S.offset, $ = S.activeTooltipIndex, D = S.tooltipAxisBandSize, R = m.getTooltipEventType(), L = (x = O.props.active) !== null && x !== void 0 ? x : T, z = m.props.layout, F = O.key || "_recharts-cursor";
        return /* @__PURE__ */ M.createElement(B4, {
          key: F,
          activeCoordinate: C,
          activePayload: A,
          activeTooltipIndex: $,
          chartName: r,
          element: O,
          isActive: L,
          layout: z,
          offset: N,
          tooltipAxisBandSize: D,
          tooltipEventType: R
        });
      }), ie(m, "renderPolarAxis", function(O, x, S) {
        var T = Tt(O, "type.axisType"), C = Tt(m.state, "".concat(T, "Map")), A = O.type.defaultProps, N = A !== void 0 ? q(q({}, A), O.props) : O.props, $ = C && C[N["".concat(T, "Id")]];
        return /* @__PURE__ */ Ue(O, q(q({}, $), {}, {
          className: _e(T, $.className),
          key: O.key || "".concat(x, "-").concat(S),
          ticks: cr($, !0)
        }));
      }), ie(m, "renderPolarGrid", function(O) {
        var x = O.props, S = x.radialLines, T = x.polarAngles, C = x.polarRadius, A = m.state, N = A.radiusAxisMap, $ = A.angleAxisMap, D = Mr(N), R = Mr($), L = R.cx, z = R.cy, F = R.innerRadius, W = R.outerRadius;
        return /* @__PURE__ */ Ue(O, {
          polarAngles: Array.isArray(T) ? T : cr(R, !0).map(function(X) {
            return X.coordinate;
          }),
          polarRadius: Array.isArray(C) ? C : cr(D, !0).map(function(X) {
            return X.coordinate;
          }),
          cx: L,
          cy: z,
          innerRadius: F,
          outerRadius: W,
          key: O.key || "polar-grid",
          radialLines: S
        });
      }), ie(m, "renderLegend", function() {
        var O = m.state.formattedGraphicalItems, x = m.props, S = x.children, T = x.width, C = x.height, A = m.props.margin || {}, N = T - (A.left || 0) - (A.right || 0), $ = l_({
          children: S,
          formattedGraphicalItems: O,
          legendWidth: N,
          legendContent: s
        });
        if (!$)
          return null;
        var D = $.item, R = h1($, F4);
        return /* @__PURE__ */ Ue(D, q(q({}, R), {}, {
          chartWidth: T,
          chartHeight: C,
          margin: A,
          onBBoxUpdate: m.handleLegendBBoxUpdate
        }));
      }), ie(m, "renderTooltip", function() {
        var O, x = m.props, S = x.children, T = x.accessibilityLayer, C = vt(S, Pt);
        if (!C)
          return null;
        var A = m.state, N = A.isTooltipActive, $ = A.activeCoordinate, D = A.activePayload, R = A.activeLabel, L = A.offset, z = (O = C.props.active) !== null && O !== void 0 ? O : N;
        return /* @__PURE__ */ Ue(C, {
          viewBox: q(q({}, L), {}, {
            x: L.left,
            y: L.top
          }),
          active: z,
          label: R,
          payload: z ? D : [],
          coordinate: $,
          accessibilityLayer: T
        });
      }), ie(m, "renderBrush", function(O) {
        var x = m.props, S = x.margin, T = x.data, C = m.state, A = C.offset, N = C.dataStartIndex, $ = C.dataEndIndex, D = C.updateId;
        return /* @__PURE__ */ Ue(O, {
          key: O.key || "_recharts-brush",
          onChange: no(m.handleBrushChange, O.props.onChange),
          data: T,
          x: H(O.props.x) ? O.props.x : A.left,
          y: H(O.props.y) ? O.props.y : A.top + A.height + A.brushBottom - (S.bottom || 0),
          width: H(O.props.width) ? O.props.width : A.width,
          startIndex: N,
          endIndex: $,
          updateId: "brush-".concat(D)
        });
      }), ie(m, "renderReferenceElement", function(O, x, S) {
        if (!O)
          return null;
        var T = m, C = T.clipPathId, A = m.state, N = A.xAxisMap, $ = A.yAxisMap, D = A.offset, R = O.type.defaultProps || {}, L = O.props, z = L.xAxisId, F = z === void 0 ? R.xAxisId : z, W = L.yAxisId, X = W === void 0 ? R.yAxisId : W;
        return /* @__PURE__ */ Ue(O, {
          key: O.key || "".concat(x, "-").concat(S),
          xAxis: N[F],
          yAxis: $[X],
          viewBox: {
            x: D.left,
            y: D.top,
            width: D.width,
            height: D.height
          },
          clipPathId: C
        });
      }), ie(m, "renderActivePoints", function(O) {
        var x = O.item, S = O.activePoint, T = O.basePoint, C = O.childIndex, A = O.isRange, N = [], $ = x.props.key, D = x.item.type.defaultProps !== void 0 ? q(q({}, x.item.type.defaultProps), x.item.props) : x.item.props, R = D.activeDot, L = D.dataKey, z = q(q({
          index: C,
          dataKey: L,
          cx: S.x,
          cy: S.y,
          r: 4,
          fill: Rp(x.item),
          strokeWidth: 2,
          stroke: "#fff",
          payload: S.payload,
          value: S.value
        }, pe(R, !1)), mo(R));
        return N.push(g.renderActiveDot(R, z, "".concat($, "-activePoint-").concat(C))), T ? N.push(g.renderActiveDot(R, q(q({}, z), {}, {
          cx: T.x,
          cy: T.y
        }), "".concat($, "-basePoint-").concat(C))) : A && N.push(null), N;
      }), ie(m, "renderGraphicChild", function(O, x, S) {
        var T = m.filterFormatItem(O, x, S);
        if (!T)
          return null;
        var C = m.getTooltipEventType(), A = m.state, N = A.isTooltipActive, $ = A.tooltipAxis, D = A.activeTooltipIndex, R = A.activeLabel, L = m.props.children, z = vt(L, Pt), F = T.props, W = F.points, X = F.isRange, J = F.baseLine, G = T.item.type.defaultProps !== void 0 ? q(q({}, T.item.type.defaultProps), T.item.props) : T.item.props, Q = G.activeDot, de = G.hide, ge = G.activeBar, qe = G.activeShape, bt = !!(!de && N && z && (Q || ge || qe)), Fe = {};
        C !== "axis" && z && z.props.trigger === "click" ? Fe = {
          onClick: no(m.handleItemMouseEnter, O.props.onClick)
        } : C !== "axis" && (Fe = {
          onMouseLeave: no(m.handleItemMouseLeave, O.props.onMouseLeave),
          onMouseEnter: no(m.handleItemMouseEnter, O.props.onMouseEnter)
        });
        var V = /* @__PURE__ */ Ue(O, q(q({}, T.props), Fe));
        function le(rr) {
          return typeof $.dataKey == "function" ? $.dataKey(rr.payload) : null;
        }
        if (bt)
          if (D >= 0) {
            var ce, B;
            if ($.dataKey && !$.allowDuplicatedCategory) {
              var Ee = typeof $.dataKey == "function" ? le : "payload.".concat($.dataKey.toString());
              ce = yo(W, Ee, R), B = X && J && yo(J, Ee, R);
            } else
              ce = W?.[D], B = X && J && J[D];
            if (qe || ge) {
              var ye = O.props.activeIndex !== void 0 ? O.props.activeIndex : D;
              return [/* @__PURE__ */ Ue(O, q(q(q({}, T.props), Fe), {}, {
                activeIndex: ye
              })), null, null];
            }
            if (!me(ce))
              return [V].concat(ra(m.renderActivePoints({
                item: T,
                activePoint: ce,
                basePoint: B,
                childIndex: D,
                isRange: X
              })));
          } else {
            var Be, je = (Be = m.getItemByXY(m.state.activeCoordinate)) !== null && Be !== void 0 ? Be : {
              graphicalItem: V
            }, rt = je.graphicalItem, zt = rt.item, er = zt === void 0 ? O : zt, bn = rt.childIndex, tr = q(q(q({}, T.props), Fe), {}, {
              activeIndex: bn
            });
            return [/* @__PURE__ */ Ue(er, tr), null, null];
          }
        return X ? [V, null, null] : [V, null];
      }), ie(m, "renderCustomized", function(O, x, S) {
        return /* @__PURE__ */ Ue(O, q(q({
          key: "recharts-customized-".concat(S)
        }, m.props), m.state));
      }), ie(m, "renderMap", {
        CartesianGrid: {
          handler: lo,
          once: !0
        },
        ReferenceArea: {
          handler: m.renderReferenceElement
        },
        ReferenceLine: {
          handler: lo
        },
        ReferenceDot: {
          handler: m.renderReferenceElement
        },
        XAxis: {
          handler: lo
        },
        YAxis: {
          handler: lo
        },
        Brush: {
          handler: m.renderBrush,
          once: !0
        },
        Bar: {
          handler: m.renderGraphicChild
        },
        Line: {
          handler: m.renderGraphicChild
        },
        Area: {
          handler: m.renderGraphicChild
        },
        Radar: {
          handler: m.renderGraphicChild
        },
        RadialBar: {
          handler: m.renderGraphicChild
        },
        Scatter: {
          handler: m.renderGraphicChild
        },
        Pie: {
          handler: m.renderGraphicChild
        },
        Funnel: {
          handler: m.renderGraphicChild
        },
        Tooltip: {
          handler: m.renderCursor,
          once: !0
        },
        PolarGrid: {
          handler: m.renderPolarGrid,
          once: !0
        },
        PolarAngleAxis: {
          handler: m.renderPolarAxis
        },
        PolarRadiusAxis: {
          handler: m.renderPolarAxis
        },
        Customized: {
          handler: m.renderCustomized
        }
      }), m.clipPathId = "".concat((w = b.id) !== null && w !== void 0 ? w : $i("recharts"), "-clip"), m.throttleTriggeredAfterMouseMove = uO(m.triggeredAfterMouseMove, (_ = b.throttleDelay) !== null && _ !== void 0 ? _ : 1e3 / 60), m.state = {}, m;
    }
    return Q4(g, p), X4(g, [{
      key: "componentDidMount",
      value: function() {
        var w, _;
        this.addListener(), this.accessibilityManager.setDetails({
          container: this.container,
          offset: {
            left: (w = this.props.margin.left) !== null && w !== void 0 ? w : 0,
            top: (_ = this.props.margin.top) !== null && _ !== void 0 ? _ : 0
          },
          coordinateList: this.state.tooltipTicks,
          mouseHandlerCallback: this.triggeredAfterMouseMove,
          layout: this.props.layout
        }), this.displayDefaultTooltip();
      }
    }, {
      key: "displayDefaultTooltip",
      value: function() {
        var w = this.props, _ = w.children, m = w.data, O = w.height, x = w.layout, S = vt(_, Pt);
        if (S) {
          var T = S.props.defaultIndex;
          if (!(typeof T != "number" || T < 0 || T > this.state.tooltipTicks.length - 1)) {
            var C = this.state.tooltipTicks[T] && this.state.tooltipTicks[T].value, A = Ph(this.state, m, T, C), N = this.state.tooltipTicks[T].coordinate, $ = (this.state.offset.top + O) / 2, D = x === "horizontal", R = D ? {
              x: N,
              y: $
            } : {
              y: N,
              x: $
            }, L = this.state.formattedGraphicalItems.find(function(F) {
              var W = F.item;
              return W.type.name === "Scatter";
            });
            L && (R = q(q({}, R), L.props.points[T].tooltipPosition), A = L.props.points[T].tooltipPayload);
            var z = {
              activeTooltipIndex: T,
              isTooltipActive: !0,
              activeLabel: C,
              activePayload: A,
              activeCoordinate: R
            };
            this.setState(z), this.renderCursor(S), this.accessibilityManager.setIndex(T);
          }
        }
      }
    }, {
      key: "getSnapshotBeforeUpdate",
      value: function(w, _) {
        if (!this.props.accessibilityLayer)
          return null;
        if (this.state.tooltipTicks !== _.tooltipTicks && this.accessibilityManager.setDetails({
          coordinateList: this.state.tooltipTicks
        }), this.props.layout !== w.layout && this.accessibilityManager.setDetails({
          layout: this.props.layout
        }), this.props.margin !== w.margin) {
          var m, O;
          this.accessibilityManager.setDetails({
            offset: {
              left: (m = this.props.margin.left) !== null && m !== void 0 ? m : 0,
              top: (O = this.props.margin.top) !== null && O !== void 0 ? O : 0
            }
          });
        }
        return null;
      }
    }, {
      key: "componentDidUpdate",
      value: function(w) {
        fd([vt(w.children, Pt)], [vt(this.props.children, Pt)]) || this.displayDefaultTooltip();
      }
    }, {
      key: "componentWillUnmount",
      value: function() {
        this.removeListener(), this.throttleTriggeredAfterMouseMove.cancel();
      }
    }, {
      key: "getTooltipEventType",
      value: function() {
        var w = vt(this.props.children, Pt);
        if (w && typeof w.props.shared == "boolean") {
          var _ = w.props.shared ? "axis" : "item";
          return u.indexOf(_) >= 0 ? _ : i;
        }
        return i;
      }
      /**
       * Get the information of mouse in chart, return null when the mouse is not in the chart
       * @param  {MousePointer} event    The event object
       * @return {Object}          Mouse data
       */
    }, {
      key: "getMouseInfo",
      value: function(w) {
        if (!this.container)
          return null;
        var _ = this.container, m = _.getBoundingClientRect(), O = pC(m), x = {
          chartX: Math.round(w.pageX - O.left),
          chartY: Math.round(w.pageY - O.top)
        }, S = m.width / _.offsetWidth || 1, T = this.inRange(x.chartX, x.chartY, S);
        if (!T)
          return null;
        var C = this.state, A = C.xAxisMap, N = C.yAxisMap, $ = this.getTooltipEventType(), D = v1(this.state, this.props.data, this.props.layout, T);
        if ($ !== "axis" && A && N) {
          var R = Mr(A).scale, L = Mr(N).scale, z = R && R.invert ? R.invert(x.chartX) : null, F = L && L.invert ? L.invert(x.chartY) : null;
          return q(q({}, x), {}, {
            xValue: z,
            yValue: F
          }, D);
        }
        return D ? q(q({}, x), D) : null;
      }
    }, {
      key: "inRange",
      value: function(w, _) {
        var m = arguments.length > 2 && arguments[2] !== void 0 ? arguments[2] : 1, O = this.props.layout, x = w / m, S = _ / m;
        if (O === "horizontal" || O === "vertical") {
          var T = this.state.offset, C = x >= T.left && x <= T.left + T.width && S >= T.top && S <= T.top + T.height;
          return C ? {
            x,
            y: S
          } : null;
        }
        var A = this.state, N = A.angleAxisMap, $ = A.radiusAxisMap;
        if (N && $) {
          var D = Mr(N);
          return jb({
            x,
            y: S
          }, D);
        }
        return null;
      }
    }, {
      key: "parseEventsOfWrapper",
      value: function() {
        var w = this.props.children, _ = this.getTooltipEventType(), m = vt(w, Pt), O = {};
        m && _ === "axis" && (m.props.trigger === "click" ? O = {
          onClick: this.handleClick
        } : O = {
          onMouseEnter: this.handleMouseEnter,
          onDoubleClick: this.handleDoubleClick,
          onMouseMove: this.handleMouseMove,
          onMouseLeave: this.handleMouseLeave,
          onTouchMove: this.handleTouchMove,
          onTouchStart: this.handleTouchStart,
          onTouchEnd: this.handleTouchEnd,
          onContextMenu: this.handleContextMenu
        });
        var x = mo(this.props, this.handleOuterEvent);
        return q(q({}, x), O);
      }
    }, {
      key: "addListener",
      value: function() {
        td.on(rd, this.handleReceiveSyncEvent);
      }
    }, {
      key: "removeListener",
      value: function() {
        td.removeListener(rd, this.handleReceiveSyncEvent);
      }
    }, {
      key: "filterFormatItem",
      value: function(w, _, m) {
        for (var O = this.state.formattedGraphicalItems, x = 0, S = O.length; x < S; x++) {
          var T = O[x];
          if (T.item === w || T.props.key === w.key || _ === fr(T.item.type) && m === T.childIndex)
            return T;
        }
        return null;
      }
    }, {
      key: "renderClipPath",
      value: function() {
        var w = this.clipPathId, _ = this.state.offset, m = _.left, O = _.top, x = _.height, S = _.width;
        return /* @__PURE__ */ M.createElement("defs", null, /* @__PURE__ */ M.createElement("clipPath", {
          id: w
        }, /* @__PURE__ */ M.createElement("rect", {
          x: m,
          y: O,
          height: x,
          width: S
        })));
      }
    }, {
      key: "getXScales",
      value: function() {
        var w = this.state.xAxisMap;
        return w ? Object.entries(w).reduce(function(_, m) {
          var O = d1(m, 2), x = O[0], S = O[1];
          return q(q({}, _), {}, ie({}, x, S.scale));
        }, {}) : null;
      }
    }, {
      key: "getYScales",
      value: function() {
        var w = this.state.yAxisMap;
        return w ? Object.entries(w).reduce(function(_, m) {
          var O = d1(m, 2), x = O[0], S = O[1];
          return q(q({}, _), {}, ie({}, x, S.scale));
        }, {}) : null;
      }
    }, {
      key: "getXScaleByAxisId",
      value: function(w) {
        var _;
        return (_ = this.state.xAxisMap) === null || _ === void 0 || (_ = _[w]) === null || _ === void 0 ? void 0 : _.scale;
      }
    }, {
      key: "getYScaleByAxisId",
      value: function(w) {
        var _;
        return (_ = this.state.yAxisMap) === null || _ === void 0 || (_ = _[w]) === null || _ === void 0 ? void 0 : _.scale;
      }
    }, {
      key: "getItemByXY",
      value: function(w) {
        var _ = this.state, m = _.formattedGraphicalItems, O = _.activeItem;
        if (m && m.length)
          for (var x = 0, S = m.length; x < S; x++) {
            var T = m[x], C = T.props, A = T.item, N = A.type.defaultProps !== void 0 ? q(q({}, A.type.defaultProps), A.props) : A.props, $ = fr(A.type);
            if ($ === "Bar") {
              var D = (C.data || []).find(function(F) {
                return Cq(w, F);
              });
              if (D)
                return {
                  graphicalItem: T,
                  payload: D
                };
            } else if ($ === "RadialBar") {
              var R = (C.data || []).find(function(F) {
                return jb(w, F);
              });
              if (R)
                return {
                  graphicalItem: T,
                  payload: R
                };
            } else if (qu(T, O) || Bu(T, O) || bi(T, O)) {
              var L = O5({
                graphicalItem: T,
                activeTooltipItem: O,
                itemData: N.data
              }), z = N.activeIndex === void 0 ? L : N.activeIndex;
              return {
                graphicalItem: q(q({}, T), {}, {
                  childIndex: z
                }),
                payload: bi(T, O) ? N.data[L] : T.props.data[L]
              };
            }
          }
        return null;
      }
    }, {
      key: "render",
      value: function() {
        var w = this;
        if (!Fy(this))
          return null;
        var _ = this.props, m = _.children, O = _.className, x = _.width, S = _.height, T = _.style, C = _.compact, A = _.title, N = _.desc, $ = h1(_, z4), D = pe($, !1);
        if (C)
          return /* @__PURE__ */ M.createElement(Vx, {
            state: this.state,
            width: this.props.width,
            height: this.props.height,
            clipPathId: this.clipPathId
          }, /* @__PURE__ */ M.createElement(hd, Mn({}, D, {
            width: x,
            height: S,
            title: A,
            desc: N
          }), this.renderClipPath(), Uy(m, this.renderMap)));
        if (this.props.accessibilityLayer) {
          var R, L;
          D.tabIndex = (R = this.props.tabIndex) !== null && R !== void 0 ? R : 0, D.role = (L = this.props.role) !== null && L !== void 0 ? L : "application", D.onKeyDown = function(F) {
            w.accessibilityManager.keyboardEvent(F);
          }, D.onFocus = function() {
            w.accessibilityManager.focus();
          };
        }
        var z = this.parseEventsOfWrapper();
        return /* @__PURE__ */ M.createElement(Vx, {
          state: this.state,
          width: this.props.width,
          height: this.props.height,
          clipPathId: this.clipPathId
        }, /* @__PURE__ */ M.createElement("div", Mn({
          className: _e("recharts-wrapper", O),
          style: q({
            position: "relative",
            cursor: "default",
            width: x,
            height: S
          }, T)
        }, z, {
          ref: function(W) {
            w.container = W;
          }
        }), /* @__PURE__ */ M.createElement(hd, Mn({}, D, {
          width: x,
          height: S,
          title: A,
          desc: N,
          style: iF
        }), this.renderClipPath(), Uy(m, this.renderMap)), this.renderLegend(), this.renderTooltip()));
      }
    }]);
  })(hu);
  ie(y, "displayName", r), ie(y, "defaultProps", q({
    layout: "horizontal",
    stackOffset: "none",
    barCategoryGap: "10%",
    barGap: 4,
    margin: {
      top: 5,
      right: 5,
      bottom: 5,
      left: 5
    },
    reverseStackOrder: !1,
    syncMethod: "index"
  }, c)), ie(y, "getDerivedStateFromProps", function(p, g) {
    var b = p.dataKey, w = p.data, _ = p.children, m = p.width, O = p.height, x = p.layout, S = p.stackOffset, T = p.margin, C = g.dataStartIndex, A = g.dataEndIndex;
    if (g.updateId === void 0) {
      var N = y1(p);
      return q(q(q({}, N), {}, {
        updateId: 0
      }, h(q(q({
        props: p
      }, N), {}, {
        updateId: 0
      }), g)), {}, {
        prevDataKey: b,
        prevData: w,
        prevWidth: m,
        prevHeight: O,
        prevLayout: x,
        prevStackOffset: S,
        prevMargin: T,
        prevChildren: _
      });
    }
    if (b !== g.prevDataKey || w !== g.prevData || m !== g.prevWidth || O !== g.prevHeight || x !== g.prevLayout || S !== g.prevStackOffset || !Nn(T, g.prevMargin)) {
      var $ = y1(p), D = {
        // (chartX, chartY) are (0,0) in default state, but we want to keep the last mouse position to avoid
        // any flickering
        chartX: g.chartX,
        chartY: g.chartY,
        // The tooltip should stay active when it was active in the previous render. If this is not
        // the case, the tooltip disappears and immediately re-appears, causing a flickering effect
        isTooltipActive: g.isTooltipActive
      }, R = q(q({}, v1(g, w, x)), {}, {
        updateId: g.updateId + 1
      }), L = q(q(q({}, $), D), R);
      return q(q(q({}, L), h(q({
        props: p
      }, L), g)), {}, {
        prevDataKey: b,
        prevData: w,
        prevWidth: m,
        prevHeight: O,
        prevLayout: x,
        prevStackOffset: S,
        prevMargin: T,
        prevChildren: _
      });
    }
    if (!fd(_, g.prevChildren)) {
      var z, F, W, X, J = vt(_, Gn), G = J && (z = (F = J.props) === null || F === void 0 ? void 0 : F.startIndex) !== null && z !== void 0 ? z : C, Q = J && (W = (X = J.props) === null || X === void 0 ? void 0 : X.endIndex) !== null && W !== void 0 ? W : A, de = G !== C || Q !== A, ge = !me(w), qe = ge && !de ? g.updateId : g.updateId + 1;
      return q(q({
        updateId: qe
      }, h(q(q({
        props: p
      }, g), {}, {
        updateId: qe,
        dataStartIndex: G,
        dataEndIndex: Q
      }), g)), {}, {
        prevChildren: _,
        dataStartIndex: G,
        dataEndIndex: Q
      });
    }
    return null;
  }), ie(y, "renderActiveDot", function(p, g, b) {
    var w;
    return /* @__PURE__ */ Lt(p) ? w = /* @__PURE__ */ Ue(p, g) : fe(p) ? w = p(g) : w = /* @__PURE__ */ M.createElement(Ip, g), /* @__PURE__ */ M.createElement(Ie, {
      className: "recharts-active-dot",
      key: b
    }, w);
  });
  var v = /* @__PURE__ */ Ir(function(g, b) {
    return /* @__PURE__ */ M.createElement(y, Mn({}, g, {
      ref: b
    }));
  });
  return v.displayName = y.displayName, v;
}, gS = mS({
  chartName: "BarChart",
  GraphicalChild: Bt,
  defaultTooltipEventType: "axis",
  validateTooltipEventTypes: ["axis", "item"],
  axisComponents: [{
    axisType: "xAxis",
    AxisComp: cn
  }, {
    axisType: "yAxis",
    AxisComp: fn
  }],
  formatAxisMap: R_
}), vF = mS({
  chartName: "AreaChart",
  GraphicalChild: Ur,
  axisComponents: [{
    axisType: "xAxis",
    AxisComp: cn
  }, {
    axisType: "yAxis",
    AxisComp: fn
  }],
  formatAxisMap: R_
});
const yF = [
  { day: "周一", 产量: 42, 目标: 45 },
  { day: "周二", 产量: 48, 目标: 45 },
  { day: "周三", 产量: 39, 目标: 45 },
  { day: "周四", 产量: 51, 目标: 45 },
  { day: "周五", 产量: 47, 目标: 45 },
  { day: "周六", 产量: 35, 目标: 45 },
  { day: "周日", 产量: 0, 目标: 0 }
], mF = [
  { month: "10月", 工资: 4820 },
  { month: "11月", 工资: 5340 },
  { month: "12月", 工资: 4980 },
  { month: "1月", 工资: 5650 },
  { month: "2月", 工资: 5120 },
  { month: "3月", 工资: 5890 }
], gF = [
  { id: 1, workOrder: "WO-20260305-001", product: "精密轴承座", qty: 48, status: "已审核", time: "今天 09:30" },
  { id: 2, workOrder: "WO-20260304-012", product: "减速箱盖板", qty: 32, status: "待审核", time: "昨天 16:45" },
  { id: 3, workOrder: "WO-20260304-008", product: "传动齿轮", qty: 55, status: "已审核", time: "昨天 11:20" }
], bF = [
  { label: "用户报工", icon: iw, path: "/work-report", color: "bg-blue-500", desc: "录入今日生产数据" },
  { label: "计件工资", icon: yu, path: "/piece-wage", color: "bg-emerald-500", desc: "查看工资明细" },
  { label: "打卡签到", icon: Fh, path: "/attendance", color: "bg-amber-500", desc: "上下班打卡记录" },
  { label: "个人资料", icon: po, path: "/profile", color: "bg-purple-500", desc: "管理个人信息" }
], xF = [
  { label: "本月产量", value: "1,248", unit: "件", icon: _E, change: "+8.2%", up: !0, color: "text-blue-600", bg: "bg-blue-50" },
  { label: "本月工资", value: "5,890", unit: "元", icon: yu, change: "+14.9%", up: !0, color: "text-emerald-600", bg: "bg-emerald-50" },
  { label: "出勤天数", value: "22", unit: "天", icon: nw, change: "满勤", up: !0, color: "text-amber-600", bg: "bg-amber-50" },
  { label: "完成率", value: "96.8", unit: "%", icon: S2, change: "+2.1%", up: !0, color: "text-purple-600", bg: "bg-purple-50" }
];
function wF() {
  const e = kh(), t = /* @__PURE__ */ new Date(), r = t.toLocaleTimeString("zh-CN", { hour: "2-digit", minute: "2-digit" }), n = t.toLocaleDateString("zh-CN", { year: "numeric", month: "long", day: "numeric", weekday: "long" });
  return /* @__PURE__ */ k("div", { className: "p-4 md:p-6 space-y-6", children: [
    /* @__PURE__ */ P("div", { className: "bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl p-6 text-white", children: /* @__PURE__ */ k("div", { className: "flex items-start justify-between", children: [
      /* @__PURE__ */ k("div", { children: [
        /* @__PURE__ */ P("p", { className: "text-blue-200 text-sm mb-1", children: n }),
        /* @__PURE__ */ P("h1", { className: "text-white mb-2", children: "早上好，张伟 👋" }),
        /* @__PURE__ */ P("p", { className: "text-blue-200 text-sm", children: "今天是您本月第22个工作日，继续加油！" })
      ] }),
      /* @__PURE__ */ k("div", { className: "text-right hidden sm:block", children: [
        /* @__PURE__ */ P("p", { className: "text-3xl font-bold text-white", children: r }),
        /* @__PURE__ */ k("div", { className: "flex items-center gap-2 mt-2 justify-end", children: [
          /* @__PURE__ */ P("span", { className: "w-2 h-2 bg-green-400 rounded-full animate-pulse" }),
          /* @__PURE__ */ P("span", { className: "text-blue-200 text-sm", children: "已打卡上班" })
        ] })
      ] })
    ] }) }),
    /* @__PURE__ */ P("div", { className: "grid grid-cols-2 lg:grid-cols-4 gap-4", children: xF.map((a) => /* @__PURE__ */ k("div", { className: "bg-white rounded-xl p-4 border border-gray-100 shadow-sm", children: [
      /* @__PURE__ */ k("div", { className: "flex items-center justify-between mb-3", children: [
        /* @__PURE__ */ P("p", { className: "text-sm text-gray-500", children: a.label }),
        /* @__PURE__ */ P("div", { className: `w-8 h-8 ${a.bg} rounded-lg flex items-center justify-center`, children: /* @__PURE__ */ P(a.icon, { size: 16, className: a.color }) })
      ] }),
      /* @__PURE__ */ k("div", { className: "flex items-end gap-1", children: [
        /* @__PURE__ */ P("span", { className: "text-2xl font-bold text-gray-900", children: a.value }),
        /* @__PURE__ */ P("span", { className: "text-sm text-gray-400 mb-0.5", children: a.unit })
      ] }),
      /* @__PURE__ */ k("div", { className: "mt-1 flex items-center gap-1", children: [
        /* @__PURE__ */ P(ld, { size: 12, className: "text-emerald-500" }),
        /* @__PURE__ */ P("span", { className: "text-xs text-emerald-600", children: a.change }),
        /* @__PURE__ */ P("span", { className: "text-xs text-gray-400", children: "较上月" })
      ] })
    ] }, a.label)) }),
    /* @__PURE__ */ k("div", { children: [
      /* @__PURE__ */ P("h2", { className: "text-gray-800 mb-3", children: "快捷入口" }),
      /* @__PURE__ */ P("div", { className: "grid grid-cols-2 md:grid-cols-4 gap-3", children: bF.map((a) => /* @__PURE__ */ k(
        "button",
        {
          onClick: () => e(a.path),
          className: "bg-white rounded-xl p-4 border border-gray-100 shadow-sm hover:shadow-md hover:border-blue-100 transition-all text-left group",
          children: [
            /* @__PURE__ */ P("div", { className: `w-10 h-10 ${a.color} rounded-xl flex items-center justify-center mb-3`, children: /* @__PURE__ */ P(a.icon, { size: 20, className: "text-white" }) }),
            /* @__PURE__ */ P("p", { className: "text-sm font-medium text-gray-900 group-hover:text-blue-600 transition-colors", children: a.label }),
            /* @__PURE__ */ P("p", { className: "text-xs text-gray-400 mt-0.5", children: a.desc })
          ]
        },
        a.path
      )) })
    ] }),
    /* @__PURE__ */ k("div", { className: "grid grid-cols-1 lg:grid-cols-2 gap-4", children: [
      /* @__PURE__ */ k("div", { className: "bg-white rounded-xl p-5 border border-gray-100 shadow-sm", children: [
        /* @__PURE__ */ k("div", { className: "flex items-center justify-between mb-4", children: [
          /* @__PURE__ */ k("div", { children: [
            /* @__PURE__ */ P("h3", { className: "text-gray-800", children: "本周产量" }),
            /* @__PURE__ */ P("p", { className: "text-xs text-gray-400 mt-0.5", children: "与目标产量对比" })
          ] }),
          /* @__PURE__ */ P(x2, { size: 16, className: "text-gray-300" })
        ] }),
        /* @__PURE__ */ P(Ed, { width: "100%", height: 180, children: /* @__PURE__ */ k(gS, { data: yF, barSize: 12, children: [
          /* @__PURE__ */ P(lu, { strokeDasharray: "3 3", stroke: "#f0f0f0" }),
          /* @__PURE__ */ P(cn, { dataKey: "day", tick: { fontSize: 11, fill: "#9ca3af" }, axisLine: !1, tickLine: !1 }),
          /* @__PURE__ */ P(fn, { tick: { fontSize: 11, fill: "#9ca3af" }, axisLine: !1, tickLine: !1 }),
          /* @__PURE__ */ P(Pt, { contentStyle: { borderRadius: 8, border: "none", boxShadow: "0 4px 12px rgba(0,0,0,0.1)" } }),
          /* @__PURE__ */ P(Bt, { dataKey: "目标", fill: "#e5e7eb", radius: [4, 4, 0, 0] }, "bar-target"),
          /* @__PURE__ */ P(Bt, { dataKey: "产量", fill: "#3b82f6", radius: [4, 4, 0, 0] }, "bar-output")
        ] }) })
      ] }),
      /* @__PURE__ */ k("div", { className: "bg-white rounded-xl p-5 border border-gray-100 shadow-sm", children: [
        /* @__PURE__ */ k("div", { className: "flex items-center justify-between mb-4", children: [
          /* @__PURE__ */ k("div", { children: [
            /* @__PURE__ */ P("h3", { className: "text-gray-800", children: "工资趋势" }),
            /* @__PURE__ */ P("p", { className: "text-xs text-gray-400 mt-0.5", children: "近6个月计件工资" })
          ] }),
          /* @__PURE__ */ P(ld, { size: 16, className: "text-emerald-400" })
        ] }),
        /* @__PURE__ */ P(Ed, { width: "100%", height: 180, children: /* @__PURE__ */ k(vF, { data: mF, children: [
          /* @__PURE__ */ P("defs", { children: /* @__PURE__ */ k("linearGradient", { id: "wageGrad", x1: "0", y1: "0", x2: "0", y2: "1", children: [
            /* @__PURE__ */ P("stop", { offset: "5%", stopColor: "#10b981", stopOpacity: 0.15 }),
            /* @__PURE__ */ P("stop", { offset: "95%", stopColor: "#10b981", stopOpacity: 0 })
          ] }) }),
          /* @__PURE__ */ P(lu, { strokeDasharray: "3 3", stroke: "#f0f0f0" }),
          /* @__PURE__ */ P(cn, { dataKey: "month", tick: { fontSize: 11, fill: "#9ca3af" }, axisLine: !1, tickLine: !1 }),
          /* @__PURE__ */ P(fn, { tick: { fontSize: 11, fill: "#9ca3af" }, axisLine: !1, tickLine: !1 }),
          /* @__PURE__ */ P(Pt, { contentStyle: { borderRadius: 8, border: "none", boxShadow: "0 4px 12px rgba(0,0,0,0.1)" }, formatter: (a) => [`¥${a}`, "工资"] }),
          /* @__PURE__ */ P(Ur, { type: "monotone", dataKey: "工资", stroke: "#10b981", strokeWidth: 2, fill: "url(#wageGrad)" }, "area-wage")
        ] }) })
      ] })
    ] }),
    /* @__PURE__ */ k("div", { className: "bg-white rounded-xl border border-gray-100 shadow-sm", children: [
      /* @__PURE__ */ k("div", { className: "flex items-center justify-between p-5 border-b border-gray-50", children: [
        /* @__PURE__ */ P("h3", { className: "text-gray-800", children: "最近报工记录" }),
        /* @__PURE__ */ k(
          "button",
          {
            onClick: () => e("/work-report"),
            className: "flex items-center gap-1 text-sm text-blue-600 hover:text-blue-700",
            children: [
              "查看全部 ",
              /* @__PURE__ */ P(O2, { size: 14 })
            ]
          }
        )
      ] }),
      /* @__PURE__ */ P("div", { className: "divide-y divide-gray-50", children: gF.map((a) => /* @__PURE__ */ k("div", { className: "flex items-center gap-4 px-5 py-3.5", children: [
        /* @__PURE__ */ P("div", { className: "flex-shrink-0", children: a.status === "已审核" ? /* @__PURE__ */ P(Bh, { size: 18, className: "text-emerald-500" }) : /* @__PURE__ */ P(D2, { size: 18, className: "text-amber-400" }) }),
        /* @__PURE__ */ k("div", { className: "flex-1 min-w-0", children: [
          /* @__PURE__ */ P("p", { className: "text-sm font-medium text-gray-900 truncate", children: a.product }),
          /* @__PURE__ */ P("p", { className: "text-xs text-gray-400", children: a.workOrder })
        ] }),
        /* @__PURE__ */ k("div", { className: "text-right flex-shrink-0", children: [
          /* @__PURE__ */ k("p", { className: "text-sm font-semibold text-gray-800", children: [
            a.qty,
            " 件"
          ] }),
          /* @__PURE__ */ P("p", { className: "text-xs text-gray-400", children: a.time })
        ] }),
        /* @__PURE__ */ P("span", { className: `flex-shrink-0 px-2 py-0.5 rounded-full text-xs ${a.status === "已审核" ? "bg-emerald-50 text-emerald-600" : "bg-amber-50 text-amber-600"}`, children: a.status })
      ] }, a.id)) })
    ] })
  ] });
}
const OF = [
  { id: 1, workOrder: "WO-20260305-001", product: "精密轴承座", process: "装配", qty: 48, unit: "个", status: "已审核", reportTime: "2026-03-05 09:30", supervisor: "李主任" },
  { id: 2, workOrder: "WO-20260304-012", product: "减速箱盖板", process: "铣削", qty: 32, unit: "件", status: "待审核", reportTime: "2026-03-04 16:45", supervisor: "王工" },
  { id: 3, workOrder: "WO-20260304-008", product: "传动齿轮", process: "车削", qty: 55, unit: "个", status: "已审核", reportTime: "2026-03-04 11:20", supervisor: "李主任" },
  { id: 4, workOrder: "WO-20260303-005", product: "液压阀块", process: "钻孔", qty: 20, unit: "个", status: "已驳回", reportTime: "2026-03-03 14:00", supervisor: "赵工" },
  { id: 5, workOrder: "WO-20260303-002", product: "连接法兰", process: "磨削", qty: 60, unit: "件", status: "已审核", reportTime: "2026-03-03 10:15", supervisor: "李主任" }
], _F = ["装配", "铣削", "车削", "钻孔", "磨削", "焊接", "热处理", "检验"], SF = ["WO-20260306-001", "WO-20260306-002", "WO-20260306-003", "WO-20260305-004"], g1 = {
  "WO-20260306-001": "精密轴承座",
  "WO-20260306-002": "减速箱盖板",
  "WO-20260306-003": "传动齿轮",
  "WO-20260305-004": "液压阀块"
}, PF = {
  已审核: "bg-emerald-50 text-emerald-600",
  待审核: "bg-amber-50 text-amber-600",
  已驳回: "bg-red-50 text-red-600"
}, AF = {
  已审核: /* @__PURE__ */ P(Bh, { size: 14, className: "text-emerald-500" }),
  待审核: /* @__PURE__ */ P(Fh, { size: 14, className: "text-amber-500" }),
  已驳回: /* @__PURE__ */ P(vo, { size: 14, className: "text-red-500" })
};
function EF() {
  const [e, t] = Oe(OF), [r, n] = Oe(""), [a, i] = Oe("全部"), [o, u] = Oe(!1), [l, s] = Oe({ workOrder: "", process: "", qty: "", remark: "" }), f = e.filter((d) => {
    const h = d.product.includes(r) || d.workOrder.includes(r), y = a === "全部" || d.status === a;
    return h && y;
  }), c = (d) => {
    d.preventDefault();
    const h = {
      id: e.length + 1,
      workOrder: l.workOrder,
      product: g1[l.workOrder] || "未知产品",
      process: l.process,
      qty: parseInt(l.qty),
      unit: "件",
      status: "待审核",
      reportTime: (/* @__PURE__ */ new Date()).toLocaleString("zh-CN").replace(/\//g, "-"),
      supervisor: "李主任"
    };
    t([h, ...e]), s({ workOrder: "", process: "", qty: "", remark: "" }), u(!1);
  };
  return /* @__PURE__ */ k("div", { className: "p-4 md:p-6 space-y-5", children: [
    /* @__PURE__ */ k("div", { className: "flex items-center justify-between", children: [
      /* @__PURE__ */ k("div", { children: [
        /* @__PURE__ */ P("h1", { children: "用户报工" }),
        /* @__PURE__ */ P("p", { className: "text-sm text-gray-400 mt-0.5", children: "记录您的生产工序完成情况" })
      ] }),
      /* @__PURE__ */ k(
        "button",
        {
          onClick: () => u(!0),
          className: "flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm",
          children: [
            /* @__PURE__ */ P(pE, { size: 16 }),
            " 新增报工"
          ]
        }
      )
    ] }),
    /* @__PURE__ */ P("div", { className: "grid grid-cols-3 gap-3", children: [
      { label: "今日报工", value: e.filter((d) => d.reportTime.startsWith("2026-03-05")).length, color: "text-blue-600", bg: "bg-blue-50" },
      { label: "待审核", value: e.filter((d) => d.status === "待审核").length, color: "text-amber-600", bg: "bg-amber-50" },
      { label: "已审核", value: e.filter((d) => d.status === "已审核").length, color: "text-emerald-600", bg: "bg-emerald-50" }
    ].map((d) => /* @__PURE__ */ k("div", { className: `${d.bg} rounded-xl p-4`, children: [
      /* @__PURE__ */ P("p", { className: `text-2xl font-bold ${d.color}`, children: d.value }),
      /* @__PURE__ */ P("p", { className: "text-xs text-gray-500 mt-1", children: d.label })
    ] }, d.label)) }),
    /* @__PURE__ */ k("div", { className: "flex gap-3 flex-wrap", children: [
      /* @__PURE__ */ k("div", { className: "flex-1 min-w-48 relative", children: [
        /* @__PURE__ */ P(gE, { size: 16, className: "absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" }),
        /* @__PURE__ */ P(
          "input",
          {
            value: r,
            onChange: (d) => n(d.target.value),
            placeholder: "搜索工单或产品...",
            className: "w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400"
          }
        )
      ] }),
      /* @__PURE__ */ P("div", { className: "flex gap-2", children: ["全部", "待审核", "已审核", "已驳回"].map((d) => /* @__PURE__ */ P(
        "button",
        {
          onClick: () => i(d),
          className: `px-3 py-2 rounded-lg text-sm transition-colors ${a === d ? "bg-blue-600 text-white" : "bg-white border border-gray-200 text-gray-600 hover:bg-gray-50"}`,
          children: d
        },
        d
      )) })
    ] }),
    /* @__PURE__ */ P("div", { className: "bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden", children: /* @__PURE__ */ k("div", { className: "overflow-x-auto", children: [
      /* @__PURE__ */ k("table", { className: "w-full text-sm", children: [
        /* @__PURE__ */ P("thead", { children: /* @__PURE__ */ k("tr", { className: "bg-gray-50 border-b border-gray-100", children: [
          /* @__PURE__ */ P("th", { className: "text-left px-5 py-3 text-gray-500 font-medium", children: "工单号" }),
          /* @__PURE__ */ P("th", { className: "text-left px-5 py-3 text-gray-500 font-medium", children: "产品名称" }),
          /* @__PURE__ */ P("th", { className: "text-left px-5 py-3 text-gray-500 font-medium", children: "工序" }),
          /* @__PURE__ */ P("th", { className: "text-right px-5 py-3 text-gray-500 font-medium", children: "数量" }),
          /* @__PURE__ */ P("th", { className: "text-left px-5 py-3 text-gray-500 font-medium", children: "报工时间" }),
          /* @__PURE__ */ P("th", { className: "text-left px-5 py-3 text-gray-500 font-medium", children: "审核人" }),
          /* @__PURE__ */ P("th", { className: "text-center px-5 py-3 text-gray-500 font-medium", children: "状态" })
        ] }) }),
        /* @__PURE__ */ P("tbody", { className: "divide-y divide-gray-50", children: f.map((d) => /* @__PURE__ */ k("tr", { className: "hover:bg-gray-50/50 transition-colors", children: [
          /* @__PURE__ */ P("td", { className: "px-5 py-3.5 font-mono text-xs text-blue-600", children: d.workOrder }),
          /* @__PURE__ */ P("td", { className: "px-5 py-3.5 font-medium text-gray-800", children: d.product }),
          /* @__PURE__ */ P("td", { className: "px-5 py-3.5 text-gray-600", children: d.process }),
          /* @__PURE__ */ k("td", { className: "px-5 py-3.5 text-right font-semibold text-gray-800", children: [
            d.qty,
            " ",
            d.unit
          ] }),
          /* @__PURE__ */ P("td", { className: "px-5 py-3.5 text-gray-500 text-xs", children: d.reportTime }),
          /* @__PURE__ */ P("td", { className: "px-5 py-3.5 text-gray-600", children: d.supervisor }),
          /* @__PURE__ */ P("td", { className: "px-5 py-3.5 text-center", children: /* @__PURE__ */ k("span", { className: `inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs ${PF[d.status]}`, children: [
            AF[d.status],
            " ",
            d.status
          ] }) })
        ] }, d.id)) })
      ] }),
      f.length === 0 && /* @__PURE__ */ k("div", { className: "text-center py-12 text-gray-400", children: [
        /* @__PURE__ */ P(TF, {}),
        /* @__PURE__ */ P("p", { className: "mt-3 text-sm", children: "暂无报工记录" })
      ] })
    ] }) }),
    o && /* @__PURE__ */ P("div", { className: "fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4", children: /* @__PURE__ */ k("div", { className: "bg-white rounded-2xl w-full max-w-md shadow-xl", children: [
      /* @__PURE__ */ k("div", { className: "flex items-center justify-between p-6 border-b border-gray-100", children: [
        /* @__PURE__ */ P("h3", { children: "新增报工" }),
        /* @__PURE__ */ P("button", { onClick: () => u(!1), className: "text-gray-400 hover:text-gray-600", children: /* @__PURE__ */ P(vo, { size: 20 }) })
      ] }),
      /* @__PURE__ */ k("form", { onSubmit: c, className: "p-6 space-y-4", children: [
        /* @__PURE__ */ k("div", { children: [
          /* @__PURE__ */ P("label", { className: "block text-sm text-gray-600 mb-1.5", children: "工单号 *" }),
          /* @__PURE__ */ k(
            "select",
            {
              required: !0,
              value: l.workOrder,
              onChange: (d) => s({ ...l, workOrder: d.target.value }),
              className: "w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400",
              children: [
                /* @__PURE__ */ P("option", { value: "", children: "请选择工单" }),
                SF.map((d) => /* @__PURE__ */ k("option", { value: d, children: [
                  d,
                  " - ",
                  g1[d]
                ] }, d))
              ]
            }
          )
        ] }),
        /* @__PURE__ */ k("div", { children: [
          /* @__PURE__ */ P("label", { className: "block text-sm text-gray-600 mb-1.5", children: "工序 *" }),
          /* @__PURE__ */ k(
            "select",
            {
              required: !0,
              value: l.process,
              onChange: (d) => s({ ...l, process: d.target.value }),
              className: "w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400",
              children: [
                /* @__PURE__ */ P("option", { value: "", children: "请选择工序" }),
                _F.map((d) => /* @__PURE__ */ P("option", { value: d, children: d }, d))
              ]
            }
          )
        ] }),
        /* @__PURE__ */ k("div", { children: [
          /* @__PURE__ */ P("label", { className: "block text-sm text-gray-600 mb-1.5", children: "完成数量 *" }),
          /* @__PURE__ */ P(
            "input",
            {
              type: "number",
              required: !0,
              min: "1",
              value: l.qty,
              onChange: (d) => s({ ...l, qty: d.target.value }),
              placeholder: "请输入完成数量",
              className: "w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400"
            }
          )
        ] }),
        /* @__PURE__ */ k("div", { children: [
          /* @__PURE__ */ P("label", { className: "block text-sm text-gray-600 mb-1.5", children: "备注" }),
          /* @__PURE__ */ P(
            "textarea",
            {
              value: l.remark,
              onChange: (d) => s({ ...l, remark: d.target.value }),
              placeholder: "可填写质量问题或其他说明...",
              rows: 3,
              className: "w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 resize-none"
            }
          )
        ] }),
        /* @__PURE__ */ k("div", { className: "flex gap-3 pt-2", children: [
          /* @__PURE__ */ P(
            "button",
            {
              type: "button",
              onClick: () => u(!1),
              className: "flex-1 px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition-colors",
              children: "取消"
            }
          ),
          /* @__PURE__ */ P(
            "button",
            {
              type: "submit",
              className: "flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 transition-colors",
              children: "提交报工"
            }
          )
        ] })
      ] })
    ] }) })
  ] });
}
function TF() {
  return /* @__PURE__ */ P("div", { className: "w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center mx-auto", children: /* @__PURE__ */ P(K2, { size: 20, className: "text-gray-300" }) });
}
const MF = ["2026-03", "2026-02", "2026-01", "2025-12", "2025-11", "2025-10"], b1 = {
  "2026-03": {
    summary: { base: 2800, piece: 2890, bonus: 300, deduct: 100, total: 5890, tax: 289, actual: 5601 },
    details: [
      { process: "装配", product: "精密轴承座", qty: 235, unitPrice: 4.2, amount: 987 },
      { process: "铣削", product: "减速箱盖板", qty: 180, unitPrice: 3.8, amount: 684 },
      { process: "车削", product: "传动齿轮", qty: 210, unitPrice: 4.5, amount: 945 },
      { process: "装配", product: "连接法兰", qty: 150, unitPrice: 1.83, amount: 274 }
    ]
  },
  "2026-02": {
    summary: { base: 2800, piece: 2120, bonus: 200, deduct: 0, total: 5120, tax: 241, actual: 4879 },
    details: [
      { process: "装配", product: "精密轴承座", qty: 190, unitPrice: 4.2, amount: 798 },
      { process: "铣削", product: "减速箱盖板", qty: 160, unitPrice: 3.8, amount: 608 },
      { process: "车削", product: "传动齿轮", qty: 160, unitPrice: 4.5, amount: 720 }
    ]
  }
}, jF = [
  { month: "10月", 基本工资: 2800, 计件工资: 2020, 奖金: 0 },
  { month: "11月", 基本工资: 2800, 计件工资: 2340, 奖金: 200 },
  { month: "12月", 基本工资: 2800, 计件工资: 1980, 奖金: 200 },
  { month: "1月", 基本工资: 2800, 计件工资: 2650, 奖金: 200 },
  { month: "2月", 基本工资: 2800, 计件工资: 2120, 奖金: 200 },
  { month: "3月", 基本工资: 2800, 计件工资: 2890, 奖金: 300 }
];
function NF() {
  const [e, t] = Oe("2026-03"), r = b1[e] || b1["2026-03"], { summary: n, details: a } = r;
  return /* @__PURE__ */ k("div", { className: "p-4 md:p-6 space-y-5", children: [
    /* @__PURE__ */ k("div", { className: "flex items-center justify-between flex-wrap gap-3", children: [
      /* @__PURE__ */ k("div", { children: [
        /* @__PURE__ */ P("h1", { children: "计件工资" }),
        /* @__PURE__ */ P("p", { className: "text-sm text-gray-400 mt-0.5", children: "查看您的工资构成与明细" })
      ] }),
      /* @__PURE__ */ k("div", { className: "flex items-center gap-3", children: [
        /* @__PURE__ */ k("div", { className: "relative", children: [
          /* @__PURE__ */ P(
            "select",
            {
              value: e,
              onChange: (i) => t(i.target.value),
              className: "appearance-none bg-white border border-gray-200 rounded-lg px-4 py-2 pr-8 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400",
              children: MF.map((i) => /* @__PURE__ */ P("option", { value: i, children: i }, i))
            }
          ),
          /* @__PURE__ */ P(C2, { size: 14, className: "absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" })
        ] }),
        /* @__PURE__ */ k("button", { className: "flex items-center gap-2 px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition-colors", children: [
          /* @__PURE__ */ P(U2, { size: 15 }),
          " 导出"
        ] })
      ] })
    ] }),
    /* @__PURE__ */ k("div", { className: "bg-gradient-to-br from-blue-600 to-blue-700 rounded-2xl p-6 text-white", children: [
      /* @__PURE__ */ k("div", { className: "flex items-start justify-between mb-6", children: [
        /* @__PURE__ */ k("div", { children: [
          /* @__PURE__ */ k("p", { className: "text-blue-200 text-sm", children: [
            e,
            " 实发工资"
          ] }),
          /* @__PURE__ */ k("p", { className: "text-4xl font-bold mt-1", children: [
            "¥",
            n.actual.toLocaleString()
          ] }),
          /* @__PURE__ */ k("p", { className: "text-blue-200 text-xs mt-1", children: [
            "税前合计 ¥",
            n.total.toLocaleString()
          ] })
        ] }),
        /* @__PURE__ */ P("div", { className: "w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center", children: /* @__PURE__ */ P(yu, { size: 22, className: "text-white" }) })
      ] }),
      /* @__PURE__ */ P("div", { className: "grid grid-cols-2 md:grid-cols-4 gap-4", children: [
        { label: "基本工资", value: n.base },
        { label: "计件工资", value: n.piece },
        { label: "绩效奖金", value: n.bonus },
        { label: "扣款", value: -n.deduct }
      ].map((i) => /* @__PURE__ */ k("div", { className: "bg-white/10 rounded-xl p-3", children: [
        /* @__PURE__ */ P("p", { className: "text-blue-200 text-xs mb-1", children: i.label }),
        /* @__PURE__ */ k("p", { className: `font-semibold ${i.value < 0 ? "text-red-300" : "text-white"}`, children: [
          i.value < 0 ? "-" : "+",
          "¥",
          Math.abs(i.value).toLocaleString()
        ] })
      ] }, i.label)) })
    ] }),
    /* @__PURE__ */ k("div", { className: "flex items-start gap-3 bg-amber-50 border border-amber-100 rounded-xl p-4", children: [
      /* @__PURE__ */ P(X2, { size: 16, className: "text-amber-500 flex-shrink-0 mt-0.5" }),
      /* @__PURE__ */ k("p", { className: "text-sm text-amber-700", children: [
        "本月个人所得税预扣 ¥",
        n.tax,
        "，实发工资已扣除相关税费。如有疑问请联系人事部门。"
      ] })
    ] }),
    /* @__PURE__ */ k("div", { className: "bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden", children: [
      /* @__PURE__ */ P("div", { className: "px-5 py-4 border-b border-gray-50", children: /* @__PURE__ */ P("h3", { className: "text-gray-800", children: "计件明细" }) }),
      /* @__PURE__ */ P("div", { className: "overflow-x-auto", children: /* @__PURE__ */ k("table", { className: "w-full text-sm", children: [
        /* @__PURE__ */ P("thead", { children: /* @__PURE__ */ k("tr", { className: "bg-gray-50 border-b border-gray-100", children: [
          /* @__PURE__ */ P("th", { className: "text-left px-5 py-3 text-gray-500 font-medium", children: "工序" }),
          /* @__PURE__ */ P("th", { className: "text-left px-5 py-3 text-gray-500 font-medium", children: "产品名称" }),
          /* @__PURE__ */ P("th", { className: "text-right px-5 py-3 text-gray-500 font-medium", children: "完成数量" }),
          /* @__PURE__ */ P("th", { className: "text-right px-5 py-3 text-gray-500 font-medium", children: "单价（元/件）" }),
          /* @__PURE__ */ P("th", { className: "text-right px-5 py-3 text-gray-500 font-medium", children: "小计" })
        ] }) }),
        /* @__PURE__ */ k("tbody", { className: "divide-y divide-gray-50", children: [
          a.map((i, o) => /* @__PURE__ */ k("tr", { className: "hover:bg-gray-50/50 transition-colors", children: [
            /* @__PURE__ */ P("td", { className: "px-5 py-3.5", children: /* @__PURE__ */ P("span", { className: "px-2.5 py-0.5 bg-blue-50 text-blue-600 rounded-full text-xs", children: i.process }) }),
            /* @__PURE__ */ P("td", { className: "px-5 py-3.5 text-gray-800", children: i.product }),
            /* @__PURE__ */ k("td", { className: "px-5 py-3.5 text-right text-gray-700", children: [
              i.qty,
              " 件"
            ] }),
            /* @__PURE__ */ k("td", { className: "px-5 py-3.5 text-right text-gray-700", children: [
              "¥",
              i.unitPrice.toFixed(2)
            ] }),
            /* @__PURE__ */ k("td", { className: "px-5 py-3.5 text-right font-semibold text-gray-900", children: [
              "¥",
              i.amount.toFixed(2)
            ] })
          ] }, o)),
          /* @__PURE__ */ k("tr", { className: "bg-gray-50", children: [
            /* @__PURE__ */ P("td", { colSpan: 4, className: "px-5 py-3 text-right font-medium text-gray-700", children: "计件工资合计" }),
            /* @__PURE__ */ k("td", { className: "px-5 py-3 text-right font-bold text-blue-600", children: [
              "¥",
              a.reduce((i, o) => i + o.amount, 0).toFixed(2)
            ] })
          ] })
        ] })
      ] }) })
    ] }),
    /* @__PURE__ */ k("div", { className: "bg-white rounded-xl border border-gray-100 shadow-sm p-5", children: [
      /* @__PURE__ */ k("div", { className: "flex items-center gap-2 mb-4", children: [
        /* @__PURE__ */ P(ld, { size: 16, className: "text-blue-500" }),
        /* @__PURE__ */ P("h3", { className: "text-gray-800", children: "近6个月工资构成趋势" })
      ] }),
      /* @__PURE__ */ P(Ed, { width: "100%", height: 220, children: /* @__PURE__ */ k(gS, { data: jF, barSize: 14, children: [
        /* @__PURE__ */ P(lu, { strokeDasharray: "3 3", stroke: "#f0f0f0" }),
        /* @__PURE__ */ P(cn, { dataKey: "month", tick: { fontSize: 11, fill: "#9ca3af" }, axisLine: !1, tickLine: !1 }),
        /* @__PURE__ */ P(fn, { tick: { fontSize: 11, fill: "#9ca3af" }, axisLine: !1, tickLine: !1 }),
        /* @__PURE__ */ P(Pt, { contentStyle: { borderRadius: 8, border: "none", boxShadow: "0 4px 12px rgba(0,0,0,0.1)" }, formatter: (i) => [`¥${i}`, ""] }),
        /* @__PURE__ */ P(rn, { wrapperStyle: { fontSize: 12, paddingTop: 8 } }),
        /* @__PURE__ */ P(Bt, { dataKey: "基本工资", fill: "#93c5fd", radius: [4, 4, 0, 0], stackId: "a" }, "bar-base"),
        /* @__PURE__ */ P(Bt, { dataKey: "计件工资", fill: "#3b82f6", radius: [0, 0, 0, 0], stackId: "a" }, "bar-piece"),
        /* @__PURE__ */ P(Bt, { dataKey: "奖金", fill: "#10b981", radius: [4, 4, 0, 0], stackId: "a" }, "bar-bonus")
      ] }) })
    ] })
  ] });
}
const x1 = {
  name: "张伟",
  empId: "EMP-2023-0142",
  department: "装配车间",
  position: "高级装配工",
  phone: "138****5678",
  email: "zhangwei@company.com",
  joinDate: "2023-03-15",
  level: "中级技工",
  supervisor: "李主任",
  shift: "白班（08:00-18:00）"
}, CF = [
  { name: "精密装配", level: 92 },
  { name: "质量检验", level: 78 },
  { name: "设备维护", level: 65 },
  { name: "工艺规范", level: 85 }
], $F = [
  { name: "高级技工证书", issuer: "人力资源和社会保障部", date: "2024-06", status: "有效" },
  { name: "安全生产培训证", issuer: "公司安全部门", date: "2025-09", status: "有效" },
  { name: "质量管理培训", issuer: "质量管理部", date: "2023-11", status: "有效" }
], RF = [
  { label: "月最佳员工", count: 3, icon: "🏆" },
  { label: "零缺陷工单", count: 128, icon: "✅" },
  { label: "连续出勤", count: 45, unit: "天", icon: "📅" },
  { label: "累计产量", count: "12,840", unit: "件", icon: "⚙️" }
];
function kF() {
  const [e, t] = Oe(x1), [r, n] = Oe(!1), [a, i] = Oe(x1), [o, u] = Oe("info"), [l, s] = Oe(!1), [f, c] = Oe({ old: "", new: "", confirm: "" }), d = [
    { key: "info", label: "基本信息" },
    { key: "skills", label: "技能评级" },
    { key: "certs", label: "证书资质" }
  ], h = () => {
    t(a), n(!1);
  };
  return /* @__PURE__ */ k("div", { className: "p-4 md:p-6 space-y-5", children: [
    /* @__PURE__ */ P("div", { className: "flex items-center justify-between", children: /* @__PURE__ */ k("div", { children: [
      /* @__PURE__ */ P("h1", { children: "个人资料" }),
      /* @__PURE__ */ P("p", { className: "text-sm text-gray-400 mt-0.5", children: "管理您的个人信息与账号设置" })
    ] }) }),
    /* @__PURE__ */ k("div", { className: "bg-white rounded-2xl border border-gray-100 shadow-sm p-6", children: [
      /* @__PURE__ */ k("div", { className: "flex items-start gap-5 flex-wrap", children: [
        /* @__PURE__ */ k("div", { className: "relative", children: [
          /* @__PURE__ */ P("div", { className: "w-20 h-20 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center", children: /* @__PURE__ */ P("span", { className: "text-white text-3xl font-bold", children: e.name[0] }) }),
          /* @__PURE__ */ P("button", { className: "absolute -bottom-1 -right-1 w-7 h-7 bg-white border border-gray-200 rounded-full flex items-center justify-center shadow-sm hover:bg-gray-50 transition-colors", children: /* @__PURE__ */ P(j2, { size: 13, className: "text-gray-500" }) })
        ] }),
        /* @__PURE__ */ k("div", { className: "flex-1 min-w-0", children: [
          /* @__PURE__ */ k("div", { className: "flex items-center gap-3 flex-wrap", children: [
            /* @__PURE__ */ P("h2", { className: "text-gray-900", children: e.name }),
            /* @__PURE__ */ P("span", { className: "px-3 py-0.5 bg-blue-50 text-blue-600 rounded-full text-xs", children: e.level }),
            /* @__PURE__ */ P("span", { className: "px-3 py-0.5 bg-emerald-50 text-emerald-600 rounded-full text-xs", children: "在职" })
          ] }),
          /* @__PURE__ */ k("p", { className: "text-gray-500 text-sm mt-1", children: [
            e.department,
            " · ",
            e.position
          ] }),
          /* @__PURE__ */ k("p", { className: "text-gray-400 text-xs mt-1", children: [
            "工号：",
            e.empId,
            " · 入职日期：",
            e.joinDate
          ] })
        ] }),
        /* @__PURE__ */ k("div", { className: "flex gap-2", children: [
          /* @__PURE__ */ k(
            "button",
            {
              onClick: () => s(!0),
              className: "flex items-center gap-2 px-3 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition-colors",
              children: [
                /* @__PURE__ */ P(Z2, { size: 14 }),
                " 修改密码"
              ]
            }
          ),
          r ? /* @__PURE__ */ k(w1, { children: [
            /* @__PURE__ */ k("button", { onClick: h, className: "flex items-center gap-2 px-3 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 transition-colors", children: [
              /* @__PURE__ */ P(yE, { size: 14 }),
              " 保存"
            ] }),
            /* @__PURE__ */ P("button", { onClick: () => {
              n(!1), i(e);
            }, className: "px-3 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition-colors", children: /* @__PURE__ */ P(vo, { size: 14 }) })
          ] }) : /* @__PURE__ */ k("button", { onClick: () => {
            n(!0), i(e);
          }, className: "flex items-center gap-2 px-3 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition-colors", children: [
            /* @__PURE__ */ P(cE, { size: 14 }),
            " 编辑"
          ] })
        ] })
      ] }),
      /* @__PURE__ */ P("div", { className: "grid grid-cols-2 md:grid-cols-4 gap-3 mt-6 pt-5 border-t border-gray-50", children: RF.map((y) => /* @__PURE__ */ k("div", { className: "text-center", children: [
        /* @__PURE__ */ P("span", { className: "text-2xl", children: y.icon }),
        /* @__PURE__ */ k("p", { className: "text-lg font-bold text-gray-800 mt-1", children: [
          y.count,
          /* @__PURE__ */ P("span", { className: "text-xs text-gray-400 ml-0.5", children: y.unit })
        ] }),
        /* @__PURE__ */ P("p", { className: "text-xs text-gray-400", children: y.label })
      ] }, y.label)) })
    ] }),
    /* @__PURE__ */ k("div", { className: "bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden", children: [
      /* @__PURE__ */ P("div", { className: "flex border-b border-gray-100", children: d.map((y) => /* @__PURE__ */ P(
        "button",
        {
          onClick: () => u(y.key),
          className: `flex-1 py-3 text-sm transition-colors ${o === y.key ? "text-blue-600 border-b-2 border-blue-600 font-medium" : "text-gray-500 hover:text-gray-700"}`,
          children: y.label
        },
        y.key
      )) }),
      /* @__PURE__ */ k("div", { className: "p-5", children: [
        o === "info" && /* @__PURE__ */ P("div", { className: "grid grid-cols-1 md:grid-cols-2 gap-4", children: [
          { label: "姓名", key: "name", icon: po },
          { label: "部门", key: "department", icon: Cv },
          { label: "岗位", key: "position", icon: nl },
          { label: "班次", key: "shift", icon: nl },
          { label: "手机号码", key: "phone", icon: dE },
          { label: "邮箱地址", key: "email", icon: aE },
          { label: "直属主管", key: "supervisor", icon: po },
          { label: "入职日期", key: "joinDate", icon: Cv }
        ].map(({ label: y, key: v, icon: p }) => /* @__PURE__ */ k("div", { className: "flex items-start gap-3", children: [
          /* @__PURE__ */ P("div", { className: "w-8 h-8 bg-gray-50 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5", children: /* @__PURE__ */ P(p, { size: 15, className: "text-gray-400" }) }),
          /* @__PURE__ */ k("div", { className: "flex-1", children: [
            /* @__PURE__ */ P("p", { className: "text-xs text-gray-400", children: y }),
            r && ["name", "phone", "email"].includes(v) ? /* @__PURE__ */ P(
              "input",
              {
                value: a[v],
                onChange: (g) => i({ ...a, [v]: g.target.value }),
                className: "mt-1 w-full px-2 py-1 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400"
              }
            ) : /* @__PURE__ */ P("p", { className: "text-sm text-gray-800 mt-0.5", children: e[v] })
          ] })
        ] }, v)) }),
        o === "skills" && /* @__PURE__ */ P("div", { className: "space-y-5", children: CF.map((y) => /* @__PURE__ */ k("div", { children: [
          /* @__PURE__ */ k("div", { className: "flex items-center justify-between mb-2", children: [
            /* @__PURE__ */ P("span", { className: "text-sm text-gray-700", children: y.name }),
            /* @__PURE__ */ k("span", { className: "text-sm font-semibold text-blue-600", children: [
              y.level,
              "分"
            ] })
          ] }),
          /* @__PURE__ */ P("div", { className: "h-2 bg-gray-100 rounded-full overflow-hidden", children: /* @__PURE__ */ P(
            "div",
            {
              className: `h-full rounded-full transition-all ${y.level >= 90 ? "bg-blue-500" : y.level >= 75 ? "bg-emerald-500" : "bg-amber-500"}`,
              style: { width: `${y.level}%` }
            }
          ) }),
          /* @__PURE__ */ P("p", { className: "text-xs text-gray-400 mt-1", children: y.level >= 90 ? "优秀" : y.level >= 75 ? "良好" : "一般" })
        ] }, y.name)) }),
        o === "certs" && /* @__PURE__ */ P("div", { className: "space-y-3", children: $F.map((y, v) => /* @__PURE__ */ k("div", { className: "flex items-start gap-4 p-4 bg-gray-50 rounded-xl", children: [
          /* @__PURE__ */ P("div", { className: "w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center flex-shrink-0", children: /* @__PURE__ */ P(nl, { size: 18, className: "text-blue-500" }) }),
          /* @__PURE__ */ k("div", { className: "flex-1", children: [
            /* @__PURE__ */ P("p", { className: "text-sm font-medium text-gray-800", children: y.name }),
            /* @__PURE__ */ k("p", { className: "text-xs text-gray-400 mt-0.5", children: [
              y.issuer,
              " · ",
              y.date
            ] })
          ] }),
          /* @__PURE__ */ k("span", { className: "flex items-center gap-1 px-2.5 py-0.5 bg-emerald-50 text-emerald-600 rounded-full text-xs", children: [
            /* @__PURE__ */ P(Bh, { size: 11 }),
            " ",
            y.status
          ] })
        ] }, v)) })
      ] })
    ] }),
    l && /* @__PURE__ */ P("div", { className: "fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4", children: /* @__PURE__ */ k("div", { className: "bg-white rounded-2xl w-full max-w-sm shadow-xl", children: [
      /* @__PURE__ */ k("div", { className: "flex items-center justify-between p-5 border-b border-gray-100", children: [
        /* @__PURE__ */ P("h3", { children: "修改密码" }),
        /* @__PURE__ */ P("button", { onClick: () => s(!1), className: "text-gray-400 hover:text-gray-600", children: /* @__PURE__ */ P(vo, { size: 20 }) })
      ] }),
      /* @__PURE__ */ k("div", { className: "p-5 space-y-4", children: [
        [
          { label: "当前密码", key: "old", placeholder: "请输入当前密码" },
          { label: "新密码", key: "new", placeholder: "请输入新密码（至少8位）" },
          { label: "确认新密码", key: "confirm", placeholder: "请再次输入新密码" }
        ].map((y) => /* @__PURE__ */ k("div", { children: [
          /* @__PURE__ */ P("label", { className: "block text-sm text-gray-600 mb-1.5", children: y.label }),
          /* @__PURE__ */ P(
            "input",
            {
              type: "password",
              placeholder: y.placeholder,
              value: f[y.key],
              onChange: (v) => c({ ...f, [y.key]: v.target.value }),
              className: "w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400"
            }
          )
        ] }, y.key)),
        /* @__PURE__ */ k("div", { className: "flex gap-3 pt-2", children: [
          /* @__PURE__ */ P(
            "button",
            {
              onClick: () => s(!1),
              className: "flex-1 px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition-colors",
              children: "取消"
            }
          ),
          /* @__PURE__ */ P(
            "button",
            {
              onClick: () => s(!1),
              className: "flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 transition-colors",
              children: "确认修改"
            }
          )
        ] })
      ] })
    ] }) })
  ] });
}
const IF = {
  正常: "text-emerald-600 bg-emerald-50",
  迟到: "text-amber-600 bg-amber-50",
  早退: "text-orange-600 bg-orange-50",
  缺勤: "text-red-600 bg-red-50",
  休息: "text-gray-400 bg-gray-50"
}, Sn = [
  { date: "2026-03-06", checkIn: "08:52", checkOut: "--:--", status: "正常", workHours: "进行中" },
  { date: "2026-03-05", checkIn: "08:45", checkOut: "18:10", status: "正常", workHours: "9h 25m" },
  { date: "2026-03-04", checkIn: "09:15", checkOut: "18:02", status: "迟到", workHours: "8h 47m" },
  { date: "2026-03-03", checkIn: "08:50", checkOut: "17:45", status: "正常", workHours: "8h 55m" },
  { date: "2026-03-02", checkIn: "08:30", checkOut: "18:30", status: "正常", workHours: "10h 0m" },
  { date: "2026-03-01", checkIn: "--:--", checkOut: "--:--", status: "休息", workHours: "--" },
  { date: "2026-02-28", checkIn: "--:--", checkOut: "--:--", status: "休息", workHours: "--" },
  { date: "2026-02-27", checkIn: "08:58", checkOut: "17:30", status: "早退", workHours: "8h 32m" },
  { date: "2026-02-26", checkIn: "08:55", checkOut: "18:05", status: "正常", workHours: "9h 10m" },
  { date: "2026-02-25", checkIn: "08:40", checkOut: "18:20", status: "正常", workHours: "9h 40m" }
];
function DF(e, t) {
  return new Date(e, t + 1, 0).getDate();
}
function LF(e, t) {
  return new Date(e, t, 1).getDay();
}
function qF() {
  const e = /* @__PURE__ */ new Date(), [t, r] = Oe(!0), [n] = Oe("08:52"), [a, i] = Oe(null), [o, u] = Oe(2026), [l, s] = Oe(2), f = DF(o, l), c = LF(o, l), d = {};
  Sn.forEach((p) => {
    d[p.date] = p;
  });
  const h = () => {
    const p = e.toLocaleTimeString("zh-CN", { hour: "2-digit", minute: "2-digit" });
    i(p), r(!1);
  }, y = ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"], v = {
    normal: Sn.filter((p) => p.status === "正常").length,
    late: Sn.filter((p) => p.status === "迟到").length,
    early: Sn.filter((p) => p.status === "早退").length,
    absent: Sn.filter((p) => p.status === "缺勤").length
  };
  return /* @__PURE__ */ k("div", { className: "p-4 md:p-6 space-y-5", children: [
    /* @__PURE__ */ k("div", { children: [
      /* @__PURE__ */ P("h1", { children: "打卡签到" }),
      /* @__PURE__ */ P("p", { className: "text-sm text-gray-400 mt-0.5", children: "记录每日上下班打卡时间" })
    ] }),
    /* @__PURE__ */ k("div", { className: "bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl p-6 text-white", children: [
      /* @__PURE__ */ k("div", { className: "flex items-center justify-between mb-6", children: [
        /* @__PURE__ */ k("div", { children: [
          /* @__PURE__ */ P("p", { className: "text-slate-400 text-sm", children: "今天 · 2026年3月6日 星期五" }),
          /* @__PURE__ */ P("p", { className: "text-2xl font-bold mt-1", children: e.toLocaleTimeString("zh-CN", { hour: "2-digit", minute: "2-digit", second: "2-digit" }) })
        ] }),
        /* @__PURE__ */ k("div", { className: "flex items-center gap-2", children: [
          /* @__PURE__ */ P(oE, { size: 14, className: "text-slate-400" }),
          /* @__PURE__ */ P("span", { className: "text-slate-300 text-sm", children: "装配车间" })
        ] })
      ] }),
      /* @__PURE__ */ k("div", { className: "grid grid-cols-2 gap-4 mb-6", children: [
        /* @__PURE__ */ k("div", { className: "bg-white/10 rounded-xl p-4", children: [
          /* @__PURE__ */ k("div", { className: "flex items-center gap-2 mb-2", children: [
            /* @__PURE__ */ P(tE, { size: 16, className: "text-emerald-400" }),
            /* @__PURE__ */ P("span", { className: "text-slate-300 text-sm", children: "上班打卡" })
          ] }),
          /* @__PURE__ */ P("p", { className: "text-xl font-bold", children: n }),
          /* @__PURE__ */ P("p", { className: "text-xs text-slate-400 mt-1", children: "✓ 准时打卡" })
        ] }),
        /* @__PURE__ */ k("div", { className: "bg-white/10 rounded-xl p-4", children: [
          /* @__PURE__ */ k("div", { className: "flex items-center gap-2 mb-2", children: [
            /* @__PURE__ */ P(ow, { size: 16, className: "text-blue-400" }),
            /* @__PURE__ */ P("span", { className: "text-slate-300 text-sm", children: "下班打卡" })
          ] }),
          /* @__PURE__ */ P("p", { className: "text-xl font-bold", children: a || "--:--" }),
          /* @__PURE__ */ P("p", { className: "text-xs text-slate-400 mt-1", children: a ? "✓ 已打卡" : "待打卡" })
        ] })
      ] }),
      /* @__PURE__ */ P(
        "button",
        {
          onClick: h,
          disabled: !!a,
          className: `w-full py-3 rounded-xl font-medium transition-all ${a ? "bg-white/10 text-slate-400 cursor-not-allowed" : "bg-blue-500 hover:bg-blue-400 text-white active:scale-95"}`,
          children: a ? "✓ 下班打卡完成" : "下班打卡"
        }
      )
    ] }),
    /* @__PURE__ */ P("div", { className: "grid grid-cols-4 gap-3", children: [
      { label: "正常出勤", value: v.normal, color: "text-emerald-600", bg: "bg-emerald-50" },
      { label: "迟到次数", value: v.late, color: "text-amber-600", bg: "bg-amber-50" },
      { label: "早退次数", value: v.early, color: "text-orange-600", bg: "bg-orange-50" },
      { label: "缺勤次数", value: v.absent, color: "text-red-600", bg: "bg-red-50" }
    ].map((p) => /* @__PURE__ */ k("div", { className: `${p.bg} rounded-xl p-3 text-center`, children: [
      /* @__PURE__ */ P("p", { className: `text-2xl font-bold ${p.color}`, children: p.value }),
      /* @__PURE__ */ P("p", { className: "text-xs text-gray-500 mt-1", children: p.label })
    ] }, p.label)) }),
    /* @__PURE__ */ k("div", { className: "bg-white rounded-xl border border-gray-100 shadow-sm p-5", children: [
      /* @__PURE__ */ k("div", { className: "flex items-center justify-between mb-4", children: [
        /* @__PURE__ */ P("h3", { className: "text-gray-800", children: "考勤日历" }),
        /* @__PURE__ */ k("div", { className: "flex items-center gap-3", children: [
          /* @__PURE__ */ P(
            "button",
            {
              onClick: () => {
                l === 0 ? (u((p) => p - 1), s(11)) : s((p) => p - 1);
              },
              className: "p-1 text-gray-400 hover:text-gray-600",
              children: /* @__PURE__ */ P(R2, { size: 18 })
            }
          ),
          /* @__PURE__ */ k("span", { className: "text-sm font-medium text-gray-700", children: [
            o,
            "年 ",
            y[l]
          ] }),
          /* @__PURE__ */ P(
            "button",
            {
              onClick: () => {
                l === 11 ? (u((p) => p + 1), s(0)) : s((p) => p + 1);
              },
              className: "p-1 text-gray-400 hover:text-gray-600",
              children: /* @__PURE__ */ P(aw, { size: 18 })
            }
          )
        ] })
      ] }),
      /* @__PURE__ */ P("div", { className: "grid grid-cols-7 gap-1 mb-1", children: ["日", "一", "二", "三", "四", "五", "六"].map((p) => /* @__PURE__ */ P("div", { className: "text-center text-xs text-gray-400 py-1", children: p }, p)) }),
      /* @__PURE__ */ k("div", { className: "grid grid-cols-7 gap-1", children: [
        Array.from({ length: c }).map((p, g) => /* @__PURE__ */ P("div", {}, `empty-${g}`)),
        Array.from({ length: f }).map((p, g) => {
          const b = g + 1, w = `${o}-${String(l + 1).padStart(2, "0")}-${String(b).padStart(2, "0")}`, _ = d[w], m = w === "2026-03-06";
          return /* @__PURE__ */ k(
            "div",
            {
              className: `relative aspect-square rounded-lg flex flex-col items-center justify-center text-xs
                  ${m ? "ring-2 ring-blue-500" : ""}
                  ${_ ? _.status === "休息" ? "bg-gray-50" : _.status === "正常" ? "bg-emerald-50" : _.status === "迟到" ? "bg-amber-50" : _.status === "早退" ? "bg-orange-50" : "bg-red-50" : ""}
                `,
              children: [
                /* @__PURE__ */ P("span", { className: `font-medium ${m ? "text-blue-600" : _?.status === "正常" ? "text-emerald-700" : _?.status === "迟到" ? "text-amber-700" : _?.status === "早退" ? "text-orange-700" : _?.status === "缺勤" ? "text-red-700" : "text-gray-400"}`, children: b }),
                _ && _.status !== "休息" && /* @__PURE__ */ P("span", { className: "text-[9px] text-gray-400 leading-tight", children: _.status })
              ]
            },
            b
          );
        })
      ] }),
      /* @__PURE__ */ P("div", { className: "flex gap-4 mt-4 flex-wrap", children: [
        { label: "正常", color: "bg-emerald-100" },
        { label: "迟到", color: "bg-amber-100" },
        { label: "早退", color: "bg-orange-100" },
        { label: "缺勤", color: "bg-red-100" },
        { label: "休息", color: "bg-gray-100" }
      ].map((p) => /* @__PURE__ */ k("div", { className: "flex items-center gap-1.5", children: [
        /* @__PURE__ */ P("div", { className: `w-3 h-3 rounded ${p.color}` }),
        /* @__PURE__ */ P("span", { className: "text-xs text-gray-500", children: p.label })
      ] }, p.label)) })
    ] }),
    /* @__PURE__ */ k("div", { className: "bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden", children: [
      /* @__PURE__ */ P("div", { className: "px-5 py-4 border-b border-gray-50", children: /* @__PURE__ */ P("h3", { className: "text-gray-800", children: "打卡记录" }) }),
      /* @__PURE__ */ P("div", { className: "divide-y divide-gray-50", children: Sn.map((p, g) => /* @__PURE__ */ k("div", { className: "flex items-center gap-4 px-5 py-3.5", children: [
        /* @__PURE__ */ P("div", { className: "flex-shrink-0 w-10 h-10 bg-gray-50 rounded-xl flex items-center justify-center", children: /* @__PURE__ */ P(nw, { size: 18, className: "text-gray-400" }) }),
        /* @__PURE__ */ k("div", { className: "flex-1 min-w-0", children: [
          /* @__PURE__ */ P("p", { className: "text-sm font-medium text-gray-800", children: p.date }),
          /* @__PURE__ */ k("div", { className: "flex gap-3 mt-0.5", children: [
            /* @__PURE__ */ k("span", { className: "text-xs text-gray-400", children: [
              "上班 ",
              p.checkIn
            ] }),
            /* @__PURE__ */ k("span", { className: "text-xs text-gray-400", children: [
              "下班 ",
              p.checkOut
            ] })
          ] })
        ] }),
        /* @__PURE__ */ P("div", { className: "text-right flex-shrink-0", children: /* @__PURE__ */ P("p", { className: "text-sm text-gray-600", children: p.workHours }) }),
        /* @__PURE__ */ P("span", { className: `flex-shrink-0 px-2.5 py-0.5 rounded-full text-xs ${IF[p.status]}`, children: p.status })
      ] }, g)) })
    ] })
  ] });
}
const BF = a2([
  {
    path: "/",
    Component: TE,
    children: [
      { index: !0, Component: wF },
      { path: "work-report", Component: EF },
      { path: "piece-wage", Component: NF },
      { path: "profile", Component: kF },
      { path: "attendance", Component: qF }
    ]
  }
]);
function FF() {
  return /* @__PURE__ */ P(jA, { router: BF });
}
const zF = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: FF
}, Symbol.toStringTag, { value: "Module" }));
export {
  UF as Code0_8
};
