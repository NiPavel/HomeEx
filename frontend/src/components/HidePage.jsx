import styles from "./HidePage.module.css";
const HidePage = ({ hide, onClick, children }) => {
  return (
    <div onClick={onClick} className={hide ? styles.hidden : ""}>
      {children}
    </div>
  );
};

export default HidePage;
