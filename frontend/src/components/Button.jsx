const Button = ({title, onClick}) => {
    return <button className='shadow-2xl rounded-xl bg-white p-2 hover:bg-slate-300' onClick={onClick}>{title}</button>
}

export default Button;