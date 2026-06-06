import { BrowserRouter, Routes, Route, NavLink } from "react-router-dom";

import Home from "./Home";
import About from "./About";
import Contact from "./Contact";
import "./App.css";

export default function App() {
  return (
    <BrowserRouter>
      <nav className="navbar">
        <div className="logo">MySite</div>

        <div className="nav-links">
          <NavLink to="/" className="nav-item">Home</NavLink>
          <NavLink to="/about" className="nav-item">About</NavLink>
          <NavLink to="/contact" className="nav-item">Contact</NavLink>
        </div>
      </nav>

      <Routes>
        <Route path="/" element={<Home />} />
        <Route path="/about" element={<About />} />
        <Route path="/contact" element={<Contact />} />
      </Routes>
    </BrowserRouter>
  );
}