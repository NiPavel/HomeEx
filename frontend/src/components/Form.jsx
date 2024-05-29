import { useRef } from "react";

const Form = ({ label, title, action, setMessage, setError }) => {
  const input = useRef("");

  const submitFormHandler = async (event) => {
    event.preventDefault();

    if (action === "Add") {
      try {
        const response = await fetch("http://localhost:3030/add-sensor", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({
            face: input.current.value,
          }),
        });

        if (!response.ok) {
          throw new Error(
            `HTTP error! Status: ${response.status} ${response.statusText}`
          );
        }

        const res = await response.json();
        setMessage(res);
      } catch (err) {
        setError(err.message);
        console.error("Error fetching adding sensor:", err.message);
      }
    }

    if (action === "Delete") {
      try {
        const id = input.current.value;
        const response = await fetch(
          `http://localhost:3030/delete-sensor/${id}`,
          {
            method: "DELETE",
            headers: {
              "Content-Type": "application/json",
            },
          }
        );

        if (!response.ok) {
          throw new Error(
            `HTTP error! Status: ${response.status} ${response.statusText}`
          );
        }

        const res = await response.json();
        setMessage(res);
      } catch (err) {
        setError(err.message);
        console.error("Error fetching deleting sensor:", err.message);
      }
    }
  };

  return (
    <form onSubmit={submitFormHandler} className="m-4 flex flex-col">
      <span className=" self-center m-4">{title}</span>
      <div className="flex gap-x-4 w-full">
        <label htmlFor="">{label}</label>
        <input
          ref={input}
          className=" border border-black rounded-xl"
          type="text"
        />
        <button
          className=" bg-gray-200 rounded-xl p-2 hover:bg-gray-300"
          type="submit"
        >
          {action}
        </button>
      </div>
    </form>
  );
};

export default Form;
