import React from 'react'

export default function List() {
 const cars = ['Ford','Bmw','Audi',]
  return (
    <>
    <h1>My Car</h1>
    <ul>
        {cars.map((car)=><li>I am a {car}</li>)}
    </ul>
    </>
  )
}
