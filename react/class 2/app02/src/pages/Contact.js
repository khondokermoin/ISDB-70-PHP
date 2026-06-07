import React from 'react'

export default function Contact() {
  return (
    <>
    <section id="contact" className="p-5">
          <div className="container">
            <h2>Contact</h2>

            <form>
              <div className="mb-3">
                <label className="form-label">Name</label>
                <input type="text" className="form-control" />
              </div>

              <div className="mb-3">
                <label className="form-label">Email</label>
                <input type="email" className="form-control" />
              </div>

              <div className="mb-3">
                <label className="form-label">Message</label>
                <textarea className="form-control" rows="4"></textarea>
              </div>

              <button type="submit" className="btn btn-primary">
                Send
              </button>
            </form>

          </div>
        </section>
    </>
  )
}
