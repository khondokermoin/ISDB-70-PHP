import React from "react";
import { Routes, Route, Outlet, BrowserRouter } from "react-router-dom";

import Header from "./components/Header";

import Home from "./pages/Home";
import About from "./pages/About";
import Contact from "./pages/Contact";
import Footer from "./components/Footer";
import Props from "./pages/Props";
import Events from "./pages/Events";
import List from "./pages/List";
import Forms from "./pages/Forms";
import Displaydata from "./pages/Displaydata";

function Layout() {
  return (
    <>
      <Header />
      <Outlet />
      <Footer />
    </>
  );
}

export default function Allroutes() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<Layout />}>
          <Route index element={<Home />} />
          <Route path="about" element={<About />} />
          <Route path="contact" element={<Contact />} />
          <Route path="props" element={<Props />} />
          <Route path="events" element={<Events />} />
          <Route path="list" element={<List />} />
          <Route path="forms" element={<Forms />} />
          <Route path="displaydata" element={<Displaydata />} />
        </Route>
      </Routes>
    </BrowserRouter>
  );
}
