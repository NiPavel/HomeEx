import { useEffect, useState } from "react";

const Notification = ({ message, error, open }) => {
  const [isOpen, setIsOpen] = useState(true);

  const close = () => {
    setIsOpen(false);
  };

  let classSet;

  if (message) {
    classSet = "bg-green-100";
  } else {
    classSet = "bg-red-100";
  }

  useEffect(() => {
    setIsOpen(open);
  }, [message, error, open]);

  return (
    <>
      {isOpen && (
        <div
          className={`${classSet} flex justify-between absolute right-0 top-0 p-2 m-4 rounded-2xl w-64 z-50`}
        >
          <span className="">{message || error}</span>
          <button
            onClick={close}
            className=" rounded-full bg-white align-middle hover:bg-slate-100"
          >
            X
          </button>
        </div>
      )}
    </>
  );
};

export default Notification;
